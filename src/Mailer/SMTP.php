<?php

namespace Difra\Mailer;

use Difra\Envi;
use Difra\Mailer\Exception\Temp;
use Difra\Mailer\SMTP\Reply;

/**
 * Class SMTP
 * Send mail using SMTP
 * @package Difra\Mailer
 */
class SMTP extends Common
{
    /**
     * Settings
     */
    const CONNECT_TIMEOUT = 10; // connect timeout (seconds)
    const READ_TIMEOUT = 5000; // default socket read timeout (milliseconds)
    const READ_LIMIT = 10240; // maximum number of bytes to expect from server by default
    /**
     * Fields
     */
    /** @var string SMTP host */
    protected $host = 'tcp://127.0.0.1:25';
    /** @var resource */
    protected static $connections = [];
    /** @var array Flushed data */
    protected $flushed = [];

    /**
     * Load config
     * @param array $config
     */
    public function loadConfig($config)
    {
        parent::loadConfig($config);
        if (!empty($config['host'])) {
            $this->host = $config['host'];
        }
    }

    /**
     * Connect
     * @param bool $ping
     * @return resource
     * @throws Temp
     */
    protected function connect($ping = false)
    {
        // use cached connection if exists
        if (!empty(self::$connections[$this->host]) and !feof(self::$connections[$this->host])) {
            // no ping requested, return cached result
            if (!$ping) {
                return self::$connections[$this->host];
            }
            // ping server
            try {
                $this->command('NOOP');
                return self::$connections[$this->host];
            } catch (\Exception $e) {
            }
        }

        // connect
        $context = stream_context_create();
        $errorNum = 0;
        $errorString = '';
        self::$connections[$this->host] = stream_socket_client(
            $this->host,
            $errorNum,
            $errorString,
            self::CONNECT_TIMEOUT,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!self::$connections[$this->host]) {
            throw new Temp('Failed to connect SMTP host ' . $this->host);
        }
        $this->read(self::CONNECT_TIMEOUT);
        return self::$connections[$this->host];
    }

    /**
     * Write to stream
     * @param $string
     * @param bool $eol
     * @throws Temp
     */
    protected function write($string, $eol = true)
    {
        if ($eol) {
            $string .= self::EOL;
        }
        $result = fwrite(
            $this->connect(),
            $string
        );
        if (!$result) {
            throw new Temp('Error writing to SMTP socket');
        }
    }

    /**
     * Read from stream
     * @param int $timeout
     * @param int $limit
     * @param bool $parse
     * @param bool $exceptions
     * @return Reply|string
     * @throws Temp
     */
    protected function read($timeout = 5000, $limit = self::READ_LIMIT, $parse = true, $exceptions = true)
    {
        $connection = $this->connect();
//        stream_set_timeout($connection, $timeout / 1000, 1000 * ($timeout % 1000));
        $result = fgets(
            $connection,
            $limit
        );
        if (empty($result)) {
            $meta = stream_get_meta_data($connection);
            if (!empty($meta['timed_out'])) {
                fclose($connection);
                self::$connections[$this->host] = null;
                throw new Temp('Read from SMTP server timed out');
            }
            return null;
        }
        if (mb_substr($result, -2) == "\r\n") {
            $result = mb_substr($result, 0, mb_strlen($result) - 2);
        }
        return $parse ? Reply::parse($result, $exceptions) : $result;
    }

    /**
     * Flush input
     */
    protected function flush()
    {
        $connection = $this->connect();
        while (true) {
            $meta = stream_get_meta_data($connection);
            if (empty($meta['unread_bytes'])) {
                return;
            }
            $flushed = $this->read();
//            echo '- ', $flushed->getSource(), PHP_EOL;
            $this->flushed[] = $flushed;
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $command
     * @param int $timeout
     * @param bool $exceptions
     * @return Reply
     */
    protected function command($command, $timeout = self::READ_TIMEOUT, $exceptions = true)
    {
        $this->flush();
//        echo "> ", $command, PHP_EOL;
        $this->write($command);
        $extends = [];
        while (true) {
            $reply = $this->read($timeout, self::READ_LIMIT, true, $exceptions);
//            echo "< ", $reply->getSource(), PHP_EOL;
            if ($reply->isExtended()) {
                $extends[] = $reply;
                continue;
            }
            $reply->setExtends($extends);
            return $reply;
        }
    }

    /**
     * Send mail
     */
    public function send()
    {
        $this->connect(true);
        // todo: move EHLO to connect()
        $this->command('EHLO ' . Envi::getHost(true));
//        $this->command('EHLO ' . Envi::getHost(true));
        $this->command('MAIL FROM:' . $this->formatFrom(true));
        foreach ($this->formatTo(true) as $to) {
            $this->command('RCPT TO:' . $to);
        }
//        $this->command('RCPT TO:' . implode('+', $this->formatTo(true)));
        $message = $this->getHeaders(true, true) . self::EOL . $this->formatBody();
//        $this->command('CHUNKING ' . mb_strlen($message, '8bit'));
//        $this->write($message, false);
        $this->command('DATA');
        $this->write($message);
        $this->command('.');
        // todo: move to __destruct()
        $this->command('QUIT');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // todo: say goodbye (QUIT) to SMTP
    }
}
