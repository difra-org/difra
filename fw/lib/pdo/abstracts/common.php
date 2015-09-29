<?php

namespace Difra\PDO\Abstracts;

use Difra\Debugger;
use Difra\Envi;
use Difra\Exception;

/**
 * Abstract PDO adapter
 * Class Common
 * @package Difra\PDO
 */
abstract class Common
{
    /** @var \PDO */
    public $pdo = null;
    /** @var int */
    public $queries = 0;
    /** @var array|null */
    protected $config = null;
    /** @var bool */
    protected $connected = null;
    /** @var string|null */
    protected $error = null;

    /**
     * Detect if this adapter is useable
     * @return bool
     */
    public static function isAvailable()
    {
        return false;
    }

    /**
     * @param $query
     * @return \PDOStatement|false
     */
    public function prepare($query)
    {
        static $cache = [];
        if (!isset($cache[$query])) {
            return $cache[$query] = $this->pdo->prepare($query);
        }
        return $cache[$query];
    }

    /**
     * Query database
     * @param string|array $query
     * @param array $parameters
     * @throws Exception
     */
    public function query($query, $parameters = [])
    {
        $this->connect();
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        $this->lastAffectedRows = $sth->rowCount();
        $this->queries++;
        Debugger::addDBLine('DB', $query);
    }

    /**
     * Query database
     * @param string|array $query
     * @param array $parametersSet
     * @throws Exception
     */
    public function multiQuery($query, $parametersSet = [])
    {
        $this->connect();
        $sth = $this->prepare($query);
        $this->lastAffectedRows = 0;
        foreach ($parametersSet as $parameters) {
            $sth->execute($parameters);
            $this->lastAffectedRows += $sth->rowCount();
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
            throw new Exception('Database connection is not available');
        }
        $this->connected = false;
        try {
            $this->realConnect();
        } catch (Exception $ex) {
            $ex->notify();
            throw new Exception('Database connection is not available');
        }
        $this->connected = true;
    }

    /**
     * Initiate database connection
     */
    abstract protected function realConnect();

    /**
     * Escape string(s)
     * @param string|array $data
     * @return string|array
     */
    public function escape($data)
    {
        $this->connect();
        if (!is_array($data)) {
            return $this->pdo->quote((string)$data);
        }
        foreach ($data as $k => $v) {
            $data[$k] = $this->escape($v);
        }
        return $data;
    }

    /**
     * Test connection to database server
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
     * Fetch data from database
     * @param string $query
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function fetch($query, $parameters = [])
    {
        $this->connect();
        Debugger::addDBLine('DB', $query);
        $this->queries++;
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        return $sth->fetchAll($parameters);
    }

    /**
     * Fetch single row
     * @param string $query
     * @param array $parameters
     * @return array|bool
     */
    public function fetchRow($query, $parameters = [])
    {
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        return $sth->fetch();
    }

    /**
     * Fetch single row
     * @param string $query
     * @param array $parameters
     * @return array|bool
     */
    public function fetchColumn($query, $parameters = [])
    {
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        return $sth->fetchColumn();
    }

    /**
     * Fetch single cell
     * @param string $query
     * @param array $parameters
     * @return mixed|null
     */
    public function fetchOne($query, $parameters = [])
    {
        $data = $this->fetchRow($query, $parameters);
        return !empty($data) ? reset($data) : null;
    }

    /**
     * Get last inserted row id
     * @return int
     */
    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }

    private $lastAffectedRows = null;

    /**
     * Get number of affected rows
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->lastAffectedRows;
    }
}
