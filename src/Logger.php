<?php

namespace Difra;

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
        $logger = Logger\Common::get($log);
        $logger->write($message, $level ?: self::INFO);
    }

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
    const TYPE_NONE = 'none';
    const TYPE_STDOUT = 'stdout';
    const TYPE_FILE = 'file';
    const TYPE_MONGO = 'mongo';
    const TYPE_SYSLOG_UDP = 'syslog_udp';
}
