<?php

namespace Difra\MySQL\Abstracts;

use Difra\Config;
use Difra\Debugger;
use Difra\Envi;
use Difra\Exception;

/**
 * Abstract MySQL adapter
 * Class Common
 * @package Difra\MySQL
 */
abstract class Common
{
    /** @var int */
    public $queries = 0;
    /** @var array|null */
    protected $config = null;
    /** @var bool */
    protected $connected = null;
    /** @var string|null */
    protected $error = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Config::getInstance()->get('db');
        if (empty($this->config['hostname'])) {
            $this->config['hostname'] = '';
        }
        if (empty($this->config['username'])) {
            $this->config['username'] = Envi::getSubsite();
        }
        if (empty($this->config['password'])) {
            $this->config['password'] = '';
        }
        if (empty($this->config['database'])) {
            $this->config['database'] = Envi::getSubsite();
        }
    }

    /**
     * Detect if this MySQL adapter is useable
     * @return bool
     */
    public static function isAvailable()
    {
        return false;
    }

    /**
     * Query database
     * If array is passed as a parameter, queries from array will be commited in single transaction. If any query
     * fail during transaction, all transaction will be cancelled.
     * @throws Exception
     * @param string|array $query
     * @return void
     */
    public function query($query)
    {
        if (!is_array($query)) {
            $this->connect();
            $this->realQuery($query);
            $this->queries++;
            Debugger::addDBLine('MySQL', $query);
        } else {
            try {
                $this->transactionStart();
                foreach ($query as $subQuery) {
                    $this->query($subQuery);
                }
                $this->transactionCommit();
            } catch (Exception $ex) {
                $this->transactionCancel();
                throw new Exception('MySQL transaction failed because of ' . $ex->getMessage());
            }
        }
    }

    /**
     * Connect to database
     * @throws Exception
     * @return void
     */
    protected function connect()
    {
        if ($this->connected === true) {
            return;
        } elseif ($this->connected === false) {
            throw new Exception('MySQL connection is not available');
        }
        $this->connected = false;
        try {
            $this->realConnect();
        } catch (Exception $ex) {
            $ex->notify();
            throw new Exception('MySQL connection is not available: ' . $ex->getMessage());
        }
        $this->connected = true;
    }

    /**
     * Initiate database connection
     */
    abstract protected function realConnect();

    /**
     * Do query
     * @param string $query
     */
    abstract protected function realQuery($query);

    /**
     * Start transaction
     */
    protected function transactionStart()
    {
    }

    /**
     * Commit transaction
     */
    protected function transactionCommit()
    {
    }

    /**
     * Cancel transaction
     */
    protected function transactionCancel()
    {
    }

    /**
     * Escape string(s) for SQL safety
     * @param string|array $data
     * @return string|array
     */
    public function escape($data)
    {
        $this->connect();
        if (!is_array($data)) {
            return $this->realEscape((string)$data);
        }
        $t = [];
        foreach ($data as $k => $v) {
            $t[$this->escape($k)] = $this->escape((string)$v);
        }
        return $t;
    }

    /**
     * Escapes string for safe SQL usage
     * @param $string
     * @return string
     */
    abstract protected function realEscape($string);

    /**
     * Test connection to MySQL server
     * @return bool
     */
    public function isConnected()
    {
        try {
            $this->connect();
        } catch (Exception $ex) {
            return false;
        }
        return $this->connected ? true : false;
    }

    /**
     * Get MySQL error text
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Fetch data from MySQL and put into id=>row array.
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return array
     */
    public function fetchWithId(
        $query,
        /** @noinspection PhpUnusedParameterInspection */
        $replica = false
    ) {
        $this->connect();
        $result = $this->fetch($query);
        $sorted = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $sorted[$row['id']] = $row;
            }
        }
        return $sorted;
    }

    /**
     * Fetch data from database
     * @param string $query
     * @param bool $replica Allow reading data from db replica
     * @return array
     */
    public function fetch($query, $replica = false)
    {
        $this->connect();
        Debugger::addDBLine('MySQL', $query);
        $this->queries++;
        return $this->realFetch($query, $replica);
    }

    /**
     * Fetch data from database
     * @param string $query
     * @param bool $replica
     * @return array|null
     */
    abstract protected function realFetch($query, $replica = false);

    /**
     * Fetch data as XML tree
     * @param \DOMNode $node XML Node
     * @param string $query query
     * @param bool $replica Позволить читать данные из реплики
     * @return bool
     */
    public function fetchXML($node, $query, $replica = false)
    {
        $data = $this->fetch($query, $replica);
        if (empty($data)) {
            return false;
        }
        foreach ($data as $row) {
            $subnode = $node->appendChild($node->ownerDocument->createElement('item'));
            $this->getRowAsXML($subnode, $row);
        }
        return true;
    }

    /**
     * Get result row as array and put it to DOM
     * @param \DOMElement|\DOMNode $node
     * @param                      $row
     * @return bool
     */
    private function getRowAsXML($node, $row)
    {
        if (empty($row)) {
            return false;
        }
        foreach ($row as $k => $v) {
            if (trim($v) and preg_match('/^(i|s|a|o|d)(.*);/si', $v)) { // serialize!
                $arr = @unserialize($v);
                $subnode = $node->appendChild($node->ownerDocument->createElement($k));
                $this->getRowAsXML($subnode, $arr);
            } else {
                $node->setAttribute($k, $v);
            }
        }
        return true;
    }

    /**
     * Fetch single row as XML
     * @param \DOMElement $node
     * @param string $query
     * @param bool $replica
     * @return bool
     */
    public function fetchRowXML($node, $query, $replica = false)
    {
        $row = $this->fetchRow($query, $replica);
        return $this->getRowAsXML($node, $row);
    }

    /**
     * Fetch single row from MySQL
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return array|bool
     */
    public function fetchRow($query, $replica = false)
    {
        $data = $this->fetch($query, $replica);
        return isset($data[0]) ? $data[0] : false;
    }

    /**
     * Get found_rows()
     * @return int
     */
    public function getFoundRows()
    {
        return $this->fetchOne("SELECT FOUND_ROWS()");
    }

    /**
     * Fetch single cell from MySQL
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return mixed|null
     */
    public function fetchOne($query, $replica = false)
    {
        $data = $this->fetchRow($query, $replica);
        return !empty($data) ? array_shift($data) : null;
    }

    /**
     * Get last auto_increment value for last inserted row
     * @return int
     */
    abstract protected function getLastId();

    /**
     * Get affected rows number
     * @return int
     */
    abstract protected function getAffectedRows();

    /**
     * Detect if mysqlnd is available
     * @return bool
     */
    protected function isND()
    {
        static $nd = null;
        return $nd ? $nd : $nd = extension_loaded('mysqlnd');
    }
}
