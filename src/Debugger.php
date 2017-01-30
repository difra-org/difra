<?php

namespace Difra;

use Difra\Envi\Request;

/**
 * Class Debugger
 * @package Difra
 */
class Debugger
{
    /** Debugging is disabled */
    const DEBUG_DISABLED = 0;
    /** Debugging is enabled */
    const DEBUG_ENABLED = 1;
    /** Console is disabled */
    const CONSOLE_DISABLED = 0;
    /** Console is enabled, but switched off by developer */
    const CONSOLE_OFF = 1;
    /** Console is enabled */
    const CONSOLE_ON = 2; // console disabled
    /** Caches are disabled */
    const CACHES_DISABLED = 0; // console enabled, but not active
    /** Caches are enabled */
    const CACHES_ENABLED = 1; // console enabled and active
    /** Display errors */
    const ERRORS_SHOW = 1;
    /** Don't display errors */
    const ERRORS_HIDE = 0;
    /** @var int Debugger state */
    private static $enabled = self::DEBUG_DISABLED;
    /** @var int Console state */
    private static $console = self::CONSOLE_DISABLED;
    /** @var int Caches state */
    private static $caches = self::CACHES_ENABLED;
    /** @var int Display errors state */
    private static $errors = self::ERRORS_HIDE;
    /** @var array Console data */
    private static $output = [];
    /** @var bool Error flag */
    private static $hadError = false;
    /** @var string If there was a handled exception, don't capture it on shutdown */
    private static $handledByException = null;
    /** @var array Last error message captured by captureNormal (let captureShutdown skip it) */
    private static $handledByNormal = null;
    /** @var bool Shut down flag (to prevent undesired output) */
    static public $shutdown = false;

    /**
     * Is debugging enabled?
     * @return bool
     */
    public static function isEnabled()
    {
        self::init();
        return (bool)self::$enabled;
    }

    public static function init()
    {
        // run once
        static $initDone = false;
        if ($initDone) {
            return;
        }
        $initDone = true;

        self::configure();
        self::apply();
    }

    private static function configure()
    {
        // cli mode
        if (Envi::getMode() == 'cli') {
            self::$enabled = self::DEBUG_ENABLED;
            self::$console = self::CONSOLE_DISABLED;
            self::$caches = self::CACHES_DISABLED;
            self::$errors = self::ERRORS_SHOW;
            return;
        }

        // production environment
        if (Envi::isProduction()) {
            self::$enabled = self::DEBUG_DISABLED;
            self::$console = self::CONSOLE_DISABLED;
            self::$caches = self::CACHES_ENABLED;
            self::$errors = self::ERRORS_HIDE;
            set_exception_handler(['\Difra\Debugger', 'productionException']);
            return;
        }

        // got GET parameter debug=-1, emulate production but show errors
        if (isset($_GET['debug']) and $_GET['debug'] == -1) {
            self::$enabled = self::DEBUG_DISABLED;
            self::$console = self::CONSOLE_DISABLED;
            self::$errors = self::ERRORS_SHOW;
            self::$caches = self::CACHES_ENABLED;
            return;
        }

        // configure default development mode
        self::$enabled = self::DEBUG_ENABLED;
        self::$console = self::CONSOLE_ON;
        self::$caches = self::CACHES_DISABLED;
        self::$errors = self::ERRORS_SHOW;

        // console is disabled by configuration or no XSL extension is available
        if ((!is_null($confConsole = Config::getInstance()->getValue('debug', 'console')) and !$confConsole) or
            !extension_loaded('xsl')
        ) {
            self::$console = self::CONSOLE_DISABLED;
            return;
        }

        // debug is disabled by a cookie or GET parameter
        if ((isset($_GET['debug']) and !$_GET['debug']) or (isset($_COOKIE['debug']) and !$_COOKIE['debug'])) {
            self::$enabled = self::DEBUG_DISABLED;
            self::$console = self::CONSOLE_OFF;
            self::$errors = self::ERRORS_HIDE;
            return;
        }

        // console is disabled by a cookie
        if (isset($_COOKIE['debugConsole']) and !$_COOKIE['debugConsole']) {
            self::$console = self::CONSOLE_OFF;
        }

        // caches enabled by a cookie
        if (isset($_COOKIE['cachesEnabled']) and $_COOKIE['cachesEnabled']) {
            self::$caches = self::CACHES_ENABLED;
        }
    }

    private static function apply()
    {
        if (self::$errors == self::ERRORS_HIDE) {
            ini_set('display_errors', 'Off');
        } else {
            ini_set('display_errors', 'On');
            ini_set('error_reporting', E_ALL);
            ini_set('html_errors', (Envi::getMode() != 'web' or Request::isAjax()) ? 'Off' : 'On');
        }
        if (self::$console == self::CONSOLE_ON) {
            ini_set('display_errors', 'Off');
            ini_set('error_reporting', E_ALL);
            set_error_handler(['\Difra\Debugger', 'captureNormal']);
            set_exception_handler(['\Difra\Debugger', 'captureException']);
            register_shutdown_function(['\Difra\Debugger', 'captureShutdown']);
        }
    }

    /**
     * Is debugging console enabled?
     * 0 — debugging is disabled
     * 1 — debugging is enabled, but console is disabled
     * 2 — console is enabled
     * @return int
     */
    public static function isConsoleEnabled()
    {
        self::init();
        return self::$console;
    }

    /**
     * Is caching enabled?
     * @return bool
     */
    public static function isCachesEnabled()
    {
        self::init();
        return (bool)self::$caches;
    }

    /**
     * Add console log message
     * @param string $line
     */
    public static function addLine($line)
    {
        self::$output[] = [
            'class' => 'messages',
            'message' => $line,
            'timer' => self::getTimer()
        ];
    }

    /**
     * Get running time
     * @return float
     */
    public static function getTimer()
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Add console log event
     * @param string $line
     */
    public static function addEventLine($line)
    {
        self::$output[] = [
            'class' => 'events',
            'message' => $line,
            'timer' => self::getTimer()
        ];
    }

    /** @var float DB request timer */
    private static $dbTimer = null;

    /**
     * DB request prepare
     */
    public static function prepareDBLine()
    {
        if (!self::$enabled) {
            return;
        }
        self::$dbTimer = microtime(true);
    }

    /**
     * Add console log for SQL requests
     * @param string $type
     * @param string $line
     */
    public static function addDBLine($type, $line)
    {
        if (!self::$enabled) {
            return;
        }
        self::$output[] = [
            'class' => 'db',
            'type' => $type,
            'message' =>
                (self::$dbTimer ? (number_format((microtime(true) - self::$dbTimer) * 1000, 1)) . ' ms: ' : '')
                . $line,
            'timer' => self::getTimer()
        ];
        self::$dbTimer = null;
    }

    /**
     * Callback for exceptions
     * @static
     * @param Exception $exception
     * @return bool
     */
    public static function captureException($exception)
    {
        self::init();
        $err = [
            'class' => 'errors',
            'stage' => 'exception',
            'message' => $msg = $exception->getMessage(),
            'file' => $file = $exception->getFile(),
            'line' => $line = $exception->getLine(),
            'traceback' => $exception->getTrace()
        ];
        self::$handledByException = "$msg in $file:$line";
        self::addLineAsArray($err);
        return false;
    }

    /**
     * Add console log error
     * @param $array
     */
    public static function addLineAsArray($array)
    {
        if (!self::$enabled) {
            return;
        }
        if ($array['class'] == 'errors') {
            self::$hadError = true;
        }
        $array['timer'] = self::getTimer();
        self::$output[] = $array;
    }

    /**
     * When running in production environment, we may want to e-mail all exceptions
     * @param $exception
     */
    public static function productionException($exception)
    {
        Exception::sendNotification($exception);
    }

    /**
     * Callback for captureable errors
     * @static
     * @param $type
     * @param $message
     * @param $file
     * @param $line
     * @return bool
     */
    public static function captureNormal($type, $message, $file, $line)
    {
        self::$handledByNormal = $message;
        if (error_reporting() == 0) {
            return false;
        }
        $err = [
            'class' => 'errors',
            'type' => $type,
            'error' => Libs\Debug\ErrorConstants::getInstance()->getVerbalError($type),
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'stage' => 'normal'
        ];
        $err['traceback'] = debug_backtrace();
        array_shift($err['traceback']);
        self::addLineAsArray($err);
        return false;
    }

    /**
     * Callback for fatal errors
     */
    public static function captureShutdown()
    {
        if (View::$rendered) {
            return;
        }
        // was there an error?
        if (!($error = error_get_last()) and !self::$handledByException) {
            return;
        }
        if ($error) {
            // add error to console log
            if (self::$handledByNormal != $error['message']) {
                $error['error'] = Libs\Debug\ErrorConstants::getInstance()->getVerbalError($error['type']);
                $error['class'] = 'errors';
                $error['traceback'] = debug_backtrace();
                array_shift($error['traceback']);
                self::addLineAsArray($error);
            }
        }

        self::$shutdown = true;

        // view was not rendered yet, render console standalone page
        if (!View::$rendered) {
            if (!Request::isAjax()) {
                echo self::debugHTML(true);
            } else {
                echo Ajaxer::getResponse();
            }
            View::$rendered = true;
        }
    }

    /**
     * Render debug console HTML
     * @param bool $standalone Render console standalone page (looks like full screen console)
     * @return string
     */
    public static function debugHTML($standalone = false)
    {
        static $alreadyDidIt = false;
        if ($alreadyDidIt) {
            return '';
        }
        /** @var $root \DOMElement */
        $xml = new \DOMDocument();
        $root = $xml->appendChild($xml->createElement('root'));
        self::debugXML($root, $standalone);

        return View::render($xml, 'all', true, true);
    }

    /**
     * Add console data to output XML
     * @param \DOMNode|\DOMElement $node
     * @param bool $standalone
     */
    public static function debugXML($node, $standalone = false)
    {
        self::init();
        $node->setAttribute('debug', self::$enabled ? '1' : '0');
        $node->setAttribute('debugConsole', self::$console);
        $node->setAttribute('caches', self::$caches ? '1' : '0');
        if (!self::$console) {
            return;
        }
        /** @var $debugNode \DOMElement */
        $debugNode = $node->appendChild($node->ownerDocument->createElement('debug'));
        Libs\XML\DOM::array2domAttr($debugNode, self::$output, true);
        if ($standalone) {
            $node->setAttribute('standalone', 1);
        }
    }

    /**
     * Does console log contain errors?
     * @return bool
     */
    public static function hadError()
    {
        return self::$hadError;
    }

    /**
     * If page rendered too long, report to developers
     * @throws Exception
     */
    public static function checkSlow()
    {
        // TODO: merge this method with Exception::sendNotification()

        $time = self::getTimer();
        if (!$time <= 1) {
            return;
        }

        // don't send notifications on development environment
        if (!Envi::isProduction()) {
            return;
        }

        $notificationMail = self::getNotificationMail();
        // no notification mail is set
        if (!$notificationMail) {
            return;
        }

        $output = '<pre>';
        foreach (self::$output as $line) {
            if (!isset($line['type'])) {
                $line['type'] = null;
            };
            $output .= "{$line['timer']}\t{$line['class']}\t{$line['type']}\t{$line['message']}\n";
        }
        $date = date('r');
        $server = print_r($_SERVER, true);
        $post = print_r($_POST, true);
        $cookie = print_r($_COOKIE, true);
        $host = Envi::getHost();
        $uri = Envi::getUri();
        $user = Auth::getInstance()->getEmail();

        $output .= <<<MSG

Page:	$uri
Time:	$date
Host:	$host
User:	$user

\$_SERVER:
$server

\$_POST:
$post

\$_COOKIE:
$cookie
MSG;
        $output .= '</pre>';
        $mailer = Mailer::getInstance();
        $mailer->setTo(self::getNotificationMail());
        $mailer->setSubject('Slow script');
        $mailer->setBody(print_r($output, true));
        $mailer->send();
    }

    /**
     * Disable Debugger
     * (for unit tests only)
     */
    public static function disable()
    {
        self::$enabled = self::DEBUG_DISABLED;
        self::apply();
    }

    /**
     * Get e-mail for notifications
     * @return string|null
     */
    public static function getNotificationMail()
    {
        return Config::getInstance()->getValue('email', 'errors');
    }
}
