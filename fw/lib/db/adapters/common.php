<?php

namespace Difra\DB\Adapters;

use Difra\Debugger;
use Difra\Envi;
use Difra\Exception;

/**
 * Abstract Db adapter
 * Class Common
 * @package Difra\DB
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
     * Detect if this adapter is usable
     * @return bool
     * @throws Exception
     */
    public static function isAvailable()
    {
        throw new Exception(get_called_class() . '::isAvailable() is not defined');
    }

    /**
     * Returns PDO connection string ($dsn parameter for constructor)
     * @return string
     */
    abstract protected function getConnectionString();

    /**
     * Constructor
     * @param array $conf
     * @throws Exception
     */
    public function __construct($conf)
    {
        if (!static::isAvailable()) {
            throw new Exception("PDO adapter is not usable: {$conf['type']}");
        }
        $this->config = $conf;
    }

    /**
     * @param $query
     * @return \PDOStatement|false
     */
    public function prepare($query)
    {
        $this->connect();
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
            $this->pdo = new \PDO(
                $this->getConnectionString(),
                $this->config['username'],
                $this->config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        } catch (\Exception $ex) {
            Exception::sendNotification($ex);
            throw new Exception("Database connection failed: {$this->config['name']}");
        }
        $this->connected = true;
    }

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
        Debugger::addDBLine('DB', $query);
        $this->queries++;
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        return $sth->fetchAll();
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
