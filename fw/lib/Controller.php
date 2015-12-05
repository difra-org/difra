<?php

namespace Difra;

use Difra\Envi\Action;
use Difra\Envi\Request;
use Difra\View\Layout;
use Difra\View\Output;

/**
 * Abstract controller
 * Class Controller
 * @package Difra
 */
abstract class Controller
{
    /** Default web server-side caching time, seconds */
    const DEFAULT_CACHE = 60;
    /** @var array URI parts to be used as parameters */
    protected static $parameters = [];
    /** @var bool */
    public $isAjaxAction = false;
    /** @var bool|int Web server-side page caching (false = no, int = seconds, true = DEFAULT_CACHE) */
    public $cache = false;
    /** @var string */
    protected $method = null;
    /**
     * View/Output links
     */
    /** @var string */
    public $output = null;
    /** @var string */
    public $outputType = 'text/plain';
    /**
     * View/Layout links
     */
    /** @var \DOMDocument */
    public $xml;
    /** @var \DOMElement */
    public $realRoot;
    /** @var \DOMElement Root */
    public $root = null;
    /** @var \DOMElement */
    public $header = null;
    /** @var \DOMElement */
    public $footer = null;

    /**
     * Constructor
     * @param array $parameters Parameters from url (from \Difra\Envi\Action)
     */
    final public function __construct($parameters = [])
    {
        self::$parameters = $parameters;

        Layout::getInstance()->linkController($this);
        $this->output =& Output::$output;
        $this->outputType =& Output::$outputType;

        // run dispatcher
        Debugger::addLine('Started controller dispatcher');
        $this->dispatch();
        Debugger::addLine('Finished controller dispatcher');
    }

    /**
     * Controller dispatcher
     */
    public function dispatch()
    {
    }

    /**
     * Pre-init
     * Needed for skipping xml fill on error pages
     */
    final public static function init()
    {
        self::getInstance();
    }

    /**
     * Call action factory
     * @return Controller
     */
    public static function getInstance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = Action::getController();
        }
        return $instance;
    }

    /**
     * Run suitable action
     */
    final public static function run()
    {
        $controller = self::getInstance();
        $controller->chooseAction();
        if (!$controller->method) {
            throw new Exception('Controller failed to choose action method');
        }
        Debugger::addLine('Started action ' . Action::$method);
        $controller->callAction();
        Debugger::addLine('Finished action ' . Action::$method);
    }

    /**
     * Choose action
     */
    private function chooseAction()
    {
        $method = null;
        if (Request::isAjax() and Action::$methodAjaxAuth and Auth::getInstance()->isAuthorized()) {
            $this->isAjaxAction = true;
            $method = 'methodAjaxAuth';
        } elseif (Request::isAjax() and Action::$methodAjax) {
            $this->isAjaxAction = true;
            $method = 'methodAjax';
        } elseif (Action::$methodAuth and Auth::getInstance()->isAuthorized()) {
            $method = 'methodAuth';
        } elseif (Action::$method) {
            $method = 'method';
        } elseif (Request::isAjax() and Action::$methodAjaxAuth) {
            self::$parameters = [];
            throw new View\HttpError(401);
        } elseif (Action::$methodAuth) {
            self::$parameters = [];
            throw new View\HttpError(401);
        } else {
            throw new View\HttpError(404);
        }
        $this->method = $method;
    }

    /**
     * Process parameters and run action
     */
    private function callAction()
    {
        $method = $this->method;
        $actionMethod = Action::${$method};
        $actionReflection = new \ReflectionMethod($this, $actionMethod);
        $actionParameters = $actionReflection->getParameters();

        // action has no parameters? just call it.
        if (empty($actionParameters)) {
            call_user_func([$this, $actionMethod]);
            return;
        }

        // get named REQUEST_URI parameters list
        $namedParameters = [];
        foreach ($actionParameters as $parameter) {
            $class = $parameter->getClass() ? $parameter->getClass()->name : 'Difra\Param\NamedString';
            if (call_user_func(["$class", "getSource"]) == 'query' and call_user_func(["$class", "isNamed"])) {
                $namedParameters[] = $parameter->getName();
            }
        }

        // get parameter values
        $callParameters = [];
        foreach ($actionParameters as $parameter) {
            $name = $parameter->getName();
            $class = $parameter->getClass() ? $parameter->getClass()->name : 'Difra\Param\NamedString';
            switch (call_user_func(["$class", "getSource"])) {
                // query parameters
                case 'query':
                    if (call_user_func(["$class", "isNamed"])) {
                        // named parameter
                        if (sizeof(self::$parameters) >= 2 and self::$parameters[0] == $name) {
                            array_shift(self::$parameters);
                            if (!call_user_func(["$class", 'verify'], self::$parameters[0])) {
                                throw new View\HttpError(404);
                            }
                            $callParameters[$parameter->getName()] =
                                new $class(array_shift(self::$parameters));
                        } elseif (call_user_func(["$class", 'isAuto'])) {
                            $callParameters[$name] = new $class;
                        } elseif (!$parameter->isOptional()) {
                            throw new View\HttpError(404);
                        } else {
                            $callParameters[$parameter->getName()] = null;
                        }
                        array_shift($namedParameters);
                    } else {
                        // unnamed parameter
                        if (!empty(self::$parameters) and (!$parameter->isOptional() or empty($namedParameters) or
                                self::$parameters[0] != $namedParameters[0])
                        ) {
                            if (!call_user_func(["$class", 'verify'], self::$parameters[0])) {
                                throw new View\HttpError(404);
                            }
                            $callParameters[$name] = new $class(array_shift(self::$parameters));
                        } elseif (!$parameter->isOptional()) {
                            throw new View\HttpError(404);
                        } else {
                            $callParameters[$parameter->getName()] = null;
                        }
                    }
                    break;
                // ajax parameters
                case 'ajax':
                    $value = Request::getParam($name);
                    if (!is_null($value) and $value !== '') {
                        if (!call_user_func(["$class", "verify"], $value)) {
                            Ajaxer::invalid($name);
                            continue;
                        }
                        $callParameters[$name] = new $class($value);
                    } elseif (call_user_func(["$class", 'isAuto'])) {
                        $callParameters[$name] = new $class;
                    } elseif (!$parameter->isOptional()) {
                        Ajaxer::required($name);
                    } else {
                        $callParameters[$name] = null;
                    }
            }
        }
        if (!Ajaxer::hasProblem()) {
            call_user_func_array([$this, $actionMethod], $callParameters);
        }
    }

    /**
     * Set X-Accel-Expires header for web server-side caching
     * @param bool|int $ttl
     */
    public function putExpires($ttl = null)
    {
        if (Debugger::isEnabled()) {
            return;
        }
        if (is_null($ttl)) {
            $ttl = $this->cache;
        }
        if ($ttl === true) {
            $ttl = self::DEFAULT_CACHE;
        }
        if (!$ttl or !is_numeric($ttl) or $ttl < 0) {
            return;
        }
        View::addExpires($ttl);
    }

    /**
     * Check referer to prevent cross-site calls
     * Should be called manually
     * @throws Exception
     */
    public function checkReferer()
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            throw new Exception('Bad referer');
        }
        if ((substr($_SERVER['HTTP_REFERER'], 0, 7) != 'http://') and (
                substr($_SERVER['HTTP_REFERER'], 0, 8) != 'https://')
        ) {
            throw new Exception('Bad referer');
        }
        $domain = explode('://', $_SERVER['HTTP_REFERER'], 2);
        $domain = explode('/', $domain[1]);
        $domain = $domain[0] . '/';
        if (false === strpos($domain, Envi::getHost(true))) {
            throw new Exception('Bad referer');
        }
    }

    /**
     * Are unused parameters still left?
     * @return bool
     */
    public static function hasUnusedParameters()
    {
        return !empty(self::$parameters);
    }
}
