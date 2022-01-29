<?php

declare(strict_types=1);

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
    public int $queries = 0;
    /** @var array|null */
    protected ?array $config = null;
    /** @var bool */
    protected ?bool $connected = null;
    /** @var string|null */
    protected ?string $error = null;

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
    public static function isAvailable(): bool
    {
        return false;
    }

    /**
     * Query database
     * If array is passed as a parameter, queries from array will be commited in single transaction. If any query
     * fail during transaction, all transaction will be cancelled.
     * @param array|string $query
     * @return void
     * @throws \Difra\DB\Exception
     * @throws \Difra\Exception
     * @throws \Exception
     */
    public function query(array|string $query): void
    {
        if (!is_array($query)) {
            $this->connect();
            Debugger::prepareDBLine();
            $this->queries++;
            Debugger::addDBLine('MySQL', $query);
        } else {
            try {
                if (Debugger::isEnabled()) {
                    $timer = microtime(true);
                    Debugger::addDBLine('MySQL', 'Transaction start');
                }
                $this->transactionStart();
                foreach ($query as $subQuery) {
                    $this->realQuery($subQuery);
                    $this->queries++;
                    Debugger::addDBLine('MySQL', $subQuery);
                }
                $this->transactionCommit();
                if (Debugger::isEnabled()) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    Debugger::addDBLine(
                        'MySQL',
                        'Transaction commited in '
                        . (number_format((microtime(true) - $timer) * 1000, 1))
                        . 'ms'
                    );
                }
            } catch (Exception $ex) {
                $this->transactionCancel();
                Debugger::addDBLine('MySQL', 'Transaction failed: ' . $ex->getMessage());
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
     * @throws \Difra\DB\Exception
     */
    abstract protected function realConnect();

    /**
     * Do query
     * @param string $query
     * @throws \Exception
     */
    abstract protected function realQuery(string $query): void;

    /**
     * Start transaction
     * @throws \Exception
     */
    protected function transactionStart()
    {
    }

    /**
     * Commit transaction
     * @throws \Exception
     */
    protected function transactionCommit()
    {
    }

    /**
     * Cancel transaction
     * @throws \Exception
     */
    protected function transactionCancel()
    {
    }

    /**
     * Escape string(s) for SQL safety
     * @param array|string $data
     * @return string|array
     * @throws \Difra\Exception
     */
    public function escape(array|string $data): array|string
    {
        $this->connect();
        if (!is_array($data)) {
            return $this->realEscape((string)$data);
        }
        $result = [];
        foreach ($data as $key => $value) {
            $result[$this->escape($key)] = $this->escape((string)$value);
        }
        return $result;
    }

    /**
     * Escapes string for safe SQL usage
     * @param string $string
     * @return string
     * @throws \Exception
     */
    abstract protected function realEscape(string $string): string;

    /**
     * Test connection to MySQL server
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            $this->connect();
        } catch (Exception $ex) {
            return false;
        }
        return (bool)$this->connected;
    }

    /**
     * Get MySQL error text
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Fetch data from MySQL and put into id=>row array.
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return array
     * @throws \Difra\Exception
     */
    public function fetchWithId(
        string $query,
        /** @noinspection PhpUnusedParameterInspection */
        bool $replica = false
    ): array {
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
     * @throws \Difra\Exception
     */
    public function fetch(string $query, bool $replica = false): array
    {
        $this->connect();
        Debugger::prepareDBLine();
        $result = $this->realFetch($query, $replica);
        Debugger::addDBLine('MySQL', $query);
        $this->queries++;
        return $result ?? [];
    }

    /**
     * Fetch data from database
     * @param string $query
     * @param bool $replica
     * @return array|null
     */
    abstract protected function realFetch(string $query, bool $replica = false): ?array;

    /**
     * Fetch data as XML tree
     * @param \DOMElement $node XML Node
     * @param string $query query
     * @param bool $replica Позволить читать данные из реплики
     * @return bool
     * @throws \Difra\Exception
     */
    public function fetchXML(\DOMElement $node, string $query, bool $replica = false): bool
    {
        $data = $this->fetch($query, $replica);
        if (empty($data)) {
            return false;
        }
        foreach ($data as $row) {
            $this->getRowAsXML($node->appendChild($node->ownerDocument->createElement('item')), $row);
        }
        return true;
    }

    /**
     * Get result row as array and put it to DOM
     * @param \DOMElement $node
     * @param array $row
     * @return bool
     */
    private function getRowAsXML(\DOMElement $node, array $row): bool
    {
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            $node->setAttribute($key, $value);
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
    public function fetchRowXML(\DOMElement $node, string $query, bool $replica = false): bool
    {
        $row = $this->fetchRow($query, $replica);
        return $this->getRowAsXML($node, $row);
    }

    /**
     * Fetch single row from MySQL
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return array|null
     * @throws \Difra\Exception
     */
    public function fetchRow(string $query, bool $replica = false): ?array
    {
        $data = $this->fetch($query, $replica);
        return $data[0] ?? null;
    }

    /**
     * Get found_rows()
     * @return int|null
     */
    public function getFoundRows(): ?int
    {
        return $this->fetchOne('SELECT FOUND_ROWS()');
    }

    /**
     * Fetch single cell from MySQL
     * @param string $query SQL-query
     * @param bool $replica Allow reading data from MySQL replica
     * @return mixed
     * @throws \Difra\Exception
     */
    public function fetchOne(string $query, bool $replica = false): mixed
    {
        $data = $this->fetchRow($query, $replica);
        return !empty($data) ? array_shift($data) : null;
    }

    /**
     * Get last auto_increment value for last inserted row
     * @return int
     */
    abstract public function getLastId(): int;

    /**
     * Get affected rows number
     * @return int
     */
    abstract public function getAffectedRows(): int;

    /**
     * Detect if mysqlnd is available
     * @return bool
     */
    protected function isND(): bool
    {
        static $nd = null;
        return $nd ?? $nd = extension_loaded('mysqlnd');
    }
}
