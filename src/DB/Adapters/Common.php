<?php

namespace Difra\DB\Adapters;

use Difra\Debugger;

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
    /** @var bool */
    protected $transaction = false;

    /**
     * Detect if this adapter is usable
     * @return bool
     * @throws \Difra\DB\Exception
     */
    public static function isAvailable()
    {
        throw new \Difra\DB\Exception(get_called_class() . '::isAvailable() is not defined');
    }

    /**
     * Returns PDO connection string ($dsn parameter for constructor)
     * @return string
     */
    abstract protected function getConnectionString();

    /**
     * Constructor
     * @param array $conf
     * @throws \Difra\DB\Exception
     */
    public function __construct($conf)
    {
        if (!static::isAvailable()) {
            throw new \Difra\DB\Exception("PDO adapter is not usable: {$conf['type']}");
        }
        $this->config = $conf;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->transaction) {
            $this->rollBack();
        }
    }

    /**
     * @param $query
     * @return \PDOStatement|false
     */
    public function prepare($query)
    {
        $this->connect();
//        static $cache = [];
//        if (!isset($cache[$query])) {
//            return $cache[$query] = $this->pdo->prepare($query);
//        }
//        return $cache[$query];
        return $this->pdo->prepare($query);
    }

    /**
     * Query database
     * @param string|array $query
     * @param array $parameters
     * @throws \Difra\DB\Exception
     */
    public function query($query, $parameters = [])
    {
        Debugger::prepareDBLine();
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        Debugger::addDBLine('DB', $query);
        $this->lastAffectedRows = $sth->rowCount();
        $this->queries++;
    }

    /**
     * Query database
     * @param string|array $query
     * @param array $parametersSet
     * @throws \Difra\DB\Exception
     */
    public function multiQuery($query, $parametersSet = [])
    {
        Debugger::prepareDBLine();
        $sth = $this->prepare($query);
        $this->lastAffectedRows = 0;
        foreach ($parametersSet as $parameters) {
            $sth->execute($parameters);
            $this->lastAffectedRows += $sth->rowCount();
        }
        Debugger::addDBLine('DB', 'multiQuery (todo)'); // todo
    }

    /**
     * Connect to database
     * @throws \Difra\DB\Exception
     * @return void
     */
    protected function connect()
    {
        if ($this->connected === true) {
            return;
//        } elseif ($this->connected === false) {
//            throw new Exception('Database connection is not available');
        }
        $this->connected = false;
        try {
            $this->pdo = new \PDO(
                $this->getConnectionString(),
                !empty($this->config['username']) ? $this->config['username'] : null,
                !empty($this->config['password']) ? $this->config['password'] : null,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        } catch (\Exception $ex) {
            \Difra\DB\Exception::sendNotification($ex);
            throw new \Difra\DB\Exception(
                'Database connection failed' . (Debugger::isEnabled() ? ' (' . $ex->getMessage() . ')' : '')
            );
        }
        $this->connected = true;
    }

    /**
     * Escape string(s)
     * @param string|array $data
     * @param bool $noQuotes
     * @return string|array
     * @throws \Difra\DB\Exception
     */
    public function escape($data, $noQuotes = false)
    {
        $this->connect();
        if (!is_array($data)) {
            $esc = $this->pdo->quote((string)$data);
            if ($noQuotes) {
                $escLength = strlen($esc);
                if ($esc{0} == '\'' && $esc{$escLength - 1} == '\'' && $escLength > 1) {
                    return substr($esc, 1, $escLength - 2);
                }
            }
            return $esc;
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
     * @throws \Difra\DB\Exception
     */
    public function fetch($query, $parameters = [])
    {
        Debugger::prepareDBLine();
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        $result = $sth->fetchAll();
        Debugger::addDBLine('DB', $query);
        $this->queries++;
        return $result;
    }

    /**
     * Fetch single row
     * @param string $query
     * @param array $parameters
     * @return array|bool
     */
    public function fetchRow($query, $parameters = [])
    {
        Debugger::prepareDBLine();
        $sth = $this->prepare($query);
        $sth->execute($parameters);
        $result = $sth->fetch();
        Debugger::addDBLine('DB', $query);
        return $result;
    }

    /**
     * Fetch single column
     * @param string $query
     * @param array $parameters
     * @param int $column_number
     * @return array|bool
     */
    public function fetchColumn($query, $parameters = [], $column_number = 0)
    {
        Debugger::prepareDBLine();
        $sth = $this->prepare($query);
        $sth->execute($parameters);

        $data = [];
        while (($row = $sth->fetchColumn($column_number)) !== false) {
            $data[] = $row;
        }
        Debugger::addDBLine('DB', $query);
        return $data;
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

    /** @var int Last affected rows number */
    private $lastAffectedRows = null;

    /**
     * Get number of affected rows
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->lastAffectedRows;
    }

    /**
     * Start transaction
     * @return bool
     */
    public function beginTransaction()
    {
        $this->connect();
        if (Debugger::isEnabled()) {
            $this->transaction = microtime(true);
            Debugger::addDBLine('DB', 'Transaction start');
        } else {
            $this->transaction = true;
        }
        return $this->pdo->beginTransaction();
    }

    /**
     * Roll back transaction
     * @return bool
     */
    public function rollBack()
    {
        $this->transaction = false;
        $result = $this->pdo->rollBack();
        if (Debugger::isEnabled()) {
            Debugger::addDBLine('DB', 'Transaction rolled back');
        }
        return $result;
    }

    /**
     * Commit transaction
     * @return bool
     */
    public function commit()
    {
        $this->transaction = false;
        $result = $this->pdo->commit();
        if (Debugger::isEnabled()) {
            Debugger::addDBLine('DB', 'Transaction commited in ' . (number_format((microtime(true) - $this->transaction) * 1000, 1)) . ' ms');
        }
        return $result;
    }

    /**
     * Get database name
     * @return string
     */
    public function getDatabase()
    {
        return $this->config['database'] ?? null;
    }
}
