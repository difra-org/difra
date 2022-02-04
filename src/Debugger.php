<?php

declare(strict_types=1);

namespace Difra;

use Difra\Envi\Request;

/**
 * Class Debugger
 * @package Difra
 */
class Debugger
{
    /** Debugging is disabled */
    public const DEBUG_DISABLED = 0;
    /** Debugging is enabled */
    public const DEBUG_ENABLED = 1;
    /** Console is disabled */
    public const CONSOLE_DISABLED = 0;
    /** Console is enabled, but switched off by developer */
    public const CONSOLE_OFF = 1;
    /** Console is enabled */
    public const CONSOLE_ON = 2; // console disabled
    /** Caches are disabled */
    public const CACHES_DISABLED = 0; // console enabled, but not active
    /** Caches are enabled */
    public const CACHES_ENABLED = 1; // console enabled and active
    /** Display errors */
    public const ERRORS_SHOW = 1;
    /** Don't display errors */
    public const ERRORS_HIDE = 0;
    /** @var int Debugger state */
    private static int $enabled = self::DEBUG_DISABLED;
    /** @var int Console state */
    private static int $console = self::CONSOLE_DISABLED;
    /** @var int Caches state */
    private static int $caches = self::CACHES_ENABLED;
    /** @var int Display errors state */
    private static int $errors = self::ERRORS_HIDE;
    /** @var array Console data */
    private static array $output = [];
    /** @var bool Error flag */
    private static bool $hadError = false;
    /** @var string|null If there was a handled exception, don't capture it on shutdown */
    private static ?string $handledByException = null;
    /** @var string|null Last error message captured by captureNormal (let captureShutdown skip it) */
    private static ?string $handledByNormal = null;
    /** @var bool Shut down flag (to prevent undesired output) */
    public static bool $shutdown = false;

    /**
     * Is debugging enabled?
     * @return bool
     */
    public static function isEnabled(): bool
    {
        self::init();
        return (bool)self::$enabled;
    }

    /**
     * Init debugger
     */
    public static function init(): void
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

    /**
     * Configure debugger
     */
    private static function configure(): void
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
        if (isset($_GET['debug']) and $_GET['debug'] === '-1') {
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

    /**
     * Apply php settings
     */
    private static function apply(): void
    {
        if (self::$errors == self::ERRORS_HIDE) {
            ini_set('display_errors', 'Off');
        } else {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
            ini_set('html_errors', (Envi::getMode() != 'web' or Request::isAjax()) ? 'Off' : 'On');
            ini_set('log_errors', 'On');
        }
        if (self::$console == self::CONSOLE_ON) {
            ini_set('display_errors', 'Off');
            error_reporting(E_ALL);
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
    public static function isConsoleEnabled(): int
    {
        self::init();
        return self::$console;
    }

    /**
     * Is caching enabled?
     * @return bool
     */
    public static function isCachesEnabled(): bool
    {
        self::init();
        return (bool)self::$caches;
    }

    /**
     * Add console log message
     * @param string $line
     */
    public static function addLine(string $line)
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
    public static function getTimer(): float
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Add console log event
     * @param string $line
     */
    public static function addEventLine(string $line)
    {
        self::$output[] = [
            'class' => 'events',
            'message' => $line,
            'timer' => self::getTimer()
        ];
    }

    /** @var float|null DB request timer */
    private static ?float $dbTimer = null;

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
    public static function addDBLine(string $type, string $line)
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
     * @param mixed $exception
     * @return bool
     */
    public static function captureException(mixed $exception): bool
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
     */
    public static function captureNormal($type, $message, $file, $line): void
    {
        self::$handledByNormal = $message;
        if (!error_reporting()) {
            return;
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
    }

    /**
     * Callback for fatal errors
     * @throws \Difra\Exception
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
     * @throws \Difra\Exception
     */
    public static function debugHTML(bool $standalone = false): string
    {
        static $alreadyDidIt = false;
        if ($alreadyDidIt) {
            return '';
        }
        $xml = new \DOMDocument();
        $root = $xml->appendChild($xml->createElement('root'));
        self::debugXML($root, $standalone);

        $view = new View();
        $view->setTemplateInstance('all');
        return $view->process($xml);
    }

    /**
     * Add console data to output XML
     * @param \DOMElement $node
     * @param bool $standalone
     */
    public static function debugXML(\DOMElement $node, bool $standalone = false)
    {
        self::init();
        $node->setAttribute('debug', self::$enabled ? '1' : '0');
        $node->setAttribute('debugConsole', (string) self::$console);
        $node->setAttribute('caches', self::$caches ? '1' : '0');
        if (!self::$console) {
            return;
        }
        $debugNode = $node->appendChild($node->ownerDocument->createElement('debug'));
        Libs\XML\DOM::array2domAttr($debugNode, self::$output, true);
        if ($standalone) {
            $node->setAttribute('standalone', '1');
        }
    }

    /**
     * Does console log contain errors?
     * @return bool
     */
    public static function hadError(): bool
    {
        return self::$hadError;
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
    public static function getNotificationMail(): ?string
    {
        return Config::getInstance()->getValue('email', 'errors');
    }
}
