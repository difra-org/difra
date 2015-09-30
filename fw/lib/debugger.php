<?php

namespace Difra;

use Difra\Envi\Request;

/**
 * Class Debugger
 * @package Difra
 */
class Debugger
{
    const DEBUG_DISABLED = 0;
    const DEBUG_ENABLED = 1;
    const CONSOLE_DISABLED = 0;
    const CONSOLE_OFF = 1;
    const CONSOLE_ON = 2; // console disabled
    const CACHES_DISABLED = 0; // console enabled, but not active
    const CACHES_ENABLED = 1; // console enabled and active
    const ERRORS_SHOW = 0;
    const ERRORS_HIDE = 0;

    /** @var bool */
    static public $shutdown = false;

    private static $enabled = self::DEBUG_DISABLED;
    private static $console = self::CONSOLE_DISABLED;
    private static $caches = self::CACHES_ENABLED;
    private static $errors = self::ERRORS_HIDE;

    private static $output = [];
    private static $hadError = false;
    private static $handledByException = null;
    /** @var array Last error message captured by captureNormal (let captureShutdown skip it) */
    private static $handledByNormal = null;

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

    private function configure()
    {
        // production environment
        if (Envi::isProduction()) {
            self::$enabled = self::DEBUG_DISABLED;
            self::$console = self::CONSOLE_DISABLED;
            self::$caches = self::CACHES_ENABLED;
            if (Envi::getMode() == 'web') {
                self::$errors = self::ERRORS_HIDE;
                set_exception_handler(['\Difra\Debugger', 'productionException']);
            } else {
                self::$errors = self::ERRORS_SHOW;
            }
            return;
        };

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

        if (
            // console is disabled by configuration
            (!is_null($confConsole = Config::getInstance()->getValue('debug', 'console')) and !$confConsole)
            or
            // no XSL extension, can't render console
            !extension_loaded('xsl')
        ) {
            self::$console = self::CONSOLE_DISABLED;
            return;
        }

        // debug is disabled by a cookie or GET parameter
        if (
            (isset($_GET['debug']) and !$_GET['debug'])
            or
            (isset($_COOKIE['debug']) and !$_COOKIE['debug'])
        ) {
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
        if(self::$errors == self::ERRORS_HIDE) {
            ini_set('display_errors', 'Off');
        } else {
            ini_set('display_errors', 'On');
            ini_set('error_reporting', E_ALL);
            ini_set('html_errors', (Envi::getMode() != 'web' or Request::isAjax()) ? 'Off' : 'On' ? 'Off' : 'On');
        }
        if(self::$console == self::CONSOLE_ON) {
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
            'message' => $line,
            'timer' => self::getTimer()
        ];
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
     * @return string
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
        if (self::$console) {
            return;
        }
        $time = self::getTimer();
        if ($time > 1) {
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
            $user = Auth::getInstance()->data['email'];

            $output .= <<<MSG

Time:	$date
Host:	$host
Uri:	$uri
User:	$user

\$_SERVER:
$server

\$_POST:
$post

\$_COOKIE:
$cookie
MSG;
            $output .= '</pre>';
            // nooo!!! please no!!!
            // TODO: implement slow script errors e-mail setting in configuration
//			Mailer::getInstance()->sendMail('errors@a-jam.ru', 'Slow script', print_r($output, true));
        }
    }
}
