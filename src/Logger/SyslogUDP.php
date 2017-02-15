<?php

namespace Difra\Logger;

use Difra\Envi;

/**
 * Class SyslogUDP
 * @package Difra\Logger
 */
class SyslogUDP extends Common
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '514';

    /**
     * @inheritdoc
     */
    protected function realWrite($message, $level)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $date = date('c');//'M d H:i:s');
        $host = $this->config['hostname'] ?? Envi::getHost(true);
        foreach (explode("\n", $message) as $line) {
            $syslog_message = "<22>$date $host {$this->logName}:$line";
            socket_sendto(
                $socket,
                $syslog_message,
                strlen($syslog_message),
                0,
                $this->config['host'] ?? self::DEFAULT_HOST,
                $this->config['port'] ?? self::DEFAULT_PORT
            );
        }
        socket_close($socket);
    }
}
