<?php

namespace Difra\Tools;

use Difra\Auth;
use Difra\Config;
use Difra\Exception;

/**
 * Class Logger
 * @package Difra\Tools\Logger
 */
class Logger
{
    /**
     * Add log record
     * @param string $message
     * @param int $level
     * @param string $log
     */
    public static function log($message, $level = self::INFO, $log = self::DEFAULT_LOG)
    {
        $logger = self::getInstance($log);
        if (!$level) {
            $level = self::INFO;
        }
        $logger->write($message, $level);
    }

    /**
     * Settings
     */
    /** Default log name */
    const DEFAULT_LOG = 'default';
    /**
     * Logging levels
     */
    const TRACE = 1;
    const DEBUG = 2;
    const INFO = 3;
    const WARN = 4;
    const ERROR = 5;
    const FATAL = 6;
    /** @var string */
    protected $logName = 'default';
    /**
     * @var string
     * %d Date
     * %p PID
     * %u User name
     * %m Message
     */
    protected $logFormat = "[%d\t%p\t%a\t%u]\t%m";
    /** @var int Minimum log level */
    protected $logLevel = self::INFO;
    const TYPE_FILE = 'file';
    const TYPE_ECHO = 'echo';
    const TYPE_MONGO = 'mongo';
    const TYPE_NONE = 'none';
    /** @var string Log type */
    protected $type = self::TYPE_NONE;
    /** @var string Log file (for $this->type == self::TYPE_FILE) */
    protected $file = null;
    /** @var mixed Mongo settings */
    protected $mongo = null;
    const MONGO_DEFAULT_CONNECTION = 'mongodb://127.0.0.1:27017';
    const MONGO_DEFAULT_SCOPE = 'logs';
    const MONGO_DEFAULT_COLLECTION = 'main';

    /**
     * Singleton
     * @param string $log
     * @return Logger
     */
    protected static function getInstance($log)
    {
        static $instances = [];
        return isset($instances[$log]) ? $instances[$log] : $instances[$log] = new self($log);
    }

    /**
     * Constructor
     * @param string $log
     * @throws Exception
     */
    private function __construct($log)
    {
        $this->logName = $log;
        $config = Config::getInstance()->getValue('logs', $log);
        $this->type = !empty($config['type']) ? $config['type'] : self::TYPE_NONE;
        switch ($this->type) {
            case self::TYPE_NONE:
                break;
            case self::TYPE_FILE:
                if (empty($config['file'])) {
                    throw new Exception("Log instance $log is defined as file, but filename is not defined");
                }
                if (!empty($config['format'])) {
                    $this->logFormat = $config['format'];
                }
                $this->file = $config['file'];
                break;
            case self::TYPE_ECHO:
                if (!empty($config['format'])) {
                    $this->logFormat = $config['format'];
                }
                break;
            case self::TYPE_MONGO:
                $this->mongo = [];
                $this->mongo['client'] = new \MongoDB\Client(!empty($config['connection']) ? $config['connection']
                    : self::MONGO_DEFAULT_CONNECTION);
                $scope = !empty($config['scope']) ? $config['scope'] : self::MONGO_DEFAULT_SCOPE;
                $collection = !empty($config['collection']) ? $config['collection'] : self::MONGO_DEFAULT_COLLECTION;
                $this->mongo['collection'] = $this->mongo['client']->$scope->$collection;
                break;
            default:
                throw new Exception("Log type {$this->type} is not implemented");
        }
    }

    /**
     * Add log message
     * @param string $message
     * @param int $level
     * @throws Exception
     */
    protected function write($message, $level)
    {
        if ($level < $this->logLevel) {
            return;
        }
        switch ($this->type) {
            case self::TYPE_NONE:
                return;
            case self::TYPE_ECHO:
                echo $this->format($message), PHP_EOL;
                return;
            case self::TYPE_FILE:
                file_put_contents($this->file, $this->format($message) . "\n", FILE_APPEND | LOCK_EX);
                return;
            case self::TYPE_MONGO:
                $obj = $this->getLogObj($message);
                $this->mongo['collection']->insertOne($obj);
                return;
            default:
                throw new Exception("I don't know how to log to {$this->type}");
        }
    }

    /**
     * Format log string
     * @param $message
     * @return string
     */
    protected function format($message)
    {
        $obj = $this->getLogObj($message);
        $replace = [
            '%d' => $obj['date'],
            '%p' => $obj['pid'],
            '%u' => !empty($obj['user']) ? $obj['user'] : '-',
            '%a' => !empty($obj['ip']) ? $obj['ip'] : '-',
            '%m' => $obj['message'],
        ];
        $result = $this->logFormat;
        foreach ($replace as $f => $t) {
            $result = str_replace($f, $t, $result);
        }
        return $result;
    }

    /**
     * Log object
     * @param $message
     * @return array
     */
    protected function getLogObj($message)
    {
        $obj = [
            'timestamp' => time(),
            'date' => date('r'),
            'message' => $message,
            'pid' => getmypid()
        ];
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $obj['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if ($a = Auth::getInstance()->getLogin()) {
            $obj['user'] = $a;
        }
        return $obj;
    }
}
