<?php

namespace Difra\Logger;

use Difra\Auth;
use Difra\Exception;
use Difra\Logger;

/**
 * Class Common
 * @package Difra\Logger
 */
abstract class Common
{
    /**
     * Actually write log message
     * @param string $message
     * @param int $level
     * @return void
     * @throws \Difra\Exception
     */
    abstract protected function realWrite($message, $level);

    /**
     * Get logger
     * @param string $log Log name
     * @return static
     */
    public static function get($log)
    {
        static $instances = [];
        if (isset($instances[$log])) {
            return $instances[$log];
        }
        $config = \Difra\Config::getInstance()->getValue('logs', $log);
        $type = $config['type'] ?? Logger::TYPE_NONE;
        switch ($type) {
            case Logger::TYPE_STDOUT:
                return $instances[$log] = new Stdout($log, $config);
            case Logger::TYPE_FILE:
                return $instances[$log] = new File($log, $config);
            case Logger::TYPE_MONGO:
                return $instances[$log] = new Mongo($log, $config);
            case Logger::TYPE_SYSLOG_UDP:
                return $instances[$log] = new SyslogUDP($log, $config);
            case Logger::TYPE_NONE:
            default:
                return $instances[$log] = new None($log, $config);
        }
    }

    /**
     * Configuration
     * @var array
     */
    protected $config = [];
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
    protected $logLevel = Logger::INFO;

    /**
     * Write log message
     * @param $message
     * @param $level
     */
    public function write($message, $level)
    {
        if ($level < $this->logLevel) {
            return;
        }
        if (is_object($message)) {
            if (property_exists($message, '_toString')) {
                $message = (string)$message;
            } else {
                $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        $this->realWrite($message, $level);
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
     * Get log object
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

    /**
     * Constructor
     * @param string $log
     * @throws Exception
     */
    private function __construct($log, $config)
    {
        $this->logName = $log;
        if (!empty($config['format'])) {
            $this->logFormat = $config['format'];
        }
    }
}
