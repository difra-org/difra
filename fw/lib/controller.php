<?php

namespace Difra;

use Difra\Controller\Layout;
use Difra\Envi\Action;
use Difra\Envi\Request;
use Difra\Envi\Version;
use Difra\View\Exception as ViewException;

/**
 * Abstract controller
 * Class Controller
 * @package Difra
 */
abstract class Controller
{
    use Layout;

    /** Default web server-side caching time, seconds */
    const DEFAULT_CACHE = 60;
    protected static $parameters = [];
    /** @var bool */
    public $isAjaxAction = false;
    /** @var bool|int Web server-side page caching (false = no, int = seconds, true = DEFAULT_CACHE) */
    public $cache = false;

    /** @var string */
    protected $method = null;
    /** @var string */
    protected $output = null;
    /** @var string */
    protected $outputType = 'text/plain';

    /**
     * Constructor
     * @param array $parameters Parameters from url (from \Difra\Envi\Action)
     */
    final public function __construct($parameters = [])
    {
        self::$parameters = $parameters;

        $this->layoutInit();

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
     * TODO: check this
     */
    final public static function init()
    {
        self::getInstance();
    }

    /**
     * Call action factory
     * @return Controller|null
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
        if (Request::isAjax() and Action::$methodAjaxAuth and Auth::getInstance()->logged) {
            $this->isAjaxAction = true;
            $method = 'methodAjaxAuth';
        } elseif (Request::isAjax() and Action::$methodAjax) {
            $this->isAjaxAction = true;
            $method = 'methodAjax';
        } elseif (Action::$methodAuth and Auth::getInstance()->logged) {
            $method = 'methodAuth';
        } elseif (Action::$method) {
            $method = 'method';
        } elseif (Action::$methodAuth or Action::$methodAjaxAuth) {
            self::$parameters = [];
            throw new View\Exception(401);
        } else {
            throw new View\Exception(404);
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
            if (call_user_func(["$class", "getSource"]) == 'query' and call_user_func(["$class", "isNamed"])
            ) {
                $namedParameters[] = $parameter->getName();
            }
        }

        // get parameter values
        $callParameters = [];
        foreach ($actionParameters as $parameter) {
            $name = $parameter->getName();
            $class = $parameter->getClass() ? $parameter->getClass()->name : 'Difra\Param\NamedString';
            switch (call_user_func(["$class", "getSource"])) {
                case 'query':        // parameter comes from query
                    if (call_user_func(["$class", "isNamed"])) {
                        // named parameter
                        if (sizeof(self::$parameters) >= 2 and self::$parameters[0] == $name) {
                            array_shift(self::$parameters);
                            if (!call_user_func(["$class", 'verify'], self::$parameters[0])) {
                                throw new View\Exception(404);
                            }
                            $callParameters[$parameter->getName()] =
                                new $class(array_shift(self::$parameters));
                        } elseif (!$parameter->isOptional()) {
                            throw new View\Exception(404);
                        } else {
                            $callParameters[$parameter->getName()] = null;
                        }
                        array_shift($namedParameters);
                    } else {
                        // unnamed parameter
                        if (
                            !empty(self::$parameters)
                            and (
                                !$parameter->isOptional()
                                or empty($namedParameters)
                                or self::$parameters[0] != $namedParameters[0]
                            )
                        ) {
                            if (!call_user_func(["$class", 'verify'], self::$parameters[0])) {
                                throw new View\Exception(404);
                            }
                            $callParameters[$name] = new $class(array_shift(self::$parameters));
                        } elseif (!$parameter->isOptional()) {
                            throw new View\Exception(404);
                        } else {
                            $callParameters[$parameter->getName()] = null;
                        }
                    }
                    break;
                case 'ajax':        // parameters comes from ajax
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
     * Choose view depending on request type
     */
    final public static function render()
    {
        $controller = self::getInstance();
        if (!empty(self::$parameters)) {
            $controller->putExpires(true);
            throw new ViewException(404);
        } elseif (!is_null($controller->output)) {
            $controller->putExpires();
            header('Content-Type: ' . $controller->outputType . '; charset="utf-8"');
            echo $controller->output;
            View::$rendered = true;
        } elseif (Debugger::isEnabled() and isset($_GET['xml']) and $_GET['xml']) {
            if ($_GET['xml'] == '2') {
                $controller->fillXML();
            }
            header('Content-Type: text/xml; charset="utf-8"');
            $controller->xml->formatOutput = true;
            $controller->xml->encoding = 'utf-8';
            echo rawurldecode($controller->xml->saveXML());
            View::$rendered = true;
        } elseif (!View::$rendered and Request::isAjax()) {
            $controller->putExpires();
            // should be application/json, but opera doesn't understand it and offers to save file to disk
            header('Content-type: text/plain');
            echo(Ajaxer::getResponse());
            View::$rendered = true;
        } elseif (!View::$rendered) {
            $controller->putExpires();
            try {
                View::render($controller->xml);
            } catch (Exception $ex) {
                if (!Debugger::isConsoleEnabled()) {
                    throw new View\Exception(500);
                } else {
                    echo Debugger::debugHTML(true);
                    die();
                }
            }
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
     * Fill output XML with some common data
     * @param \DOMDocument|null $xml
     * @param null $instance
     */
    public function fillXML(&$xml = null, $instance = null)
    {
        if (is_null($xml)) {
            $xml = $this->xml;
            $node = $this->realRoot;
        } else {
            $node = $xml->documentElement;
        }
        Debugger::addLine('Filling XML data for render: Started');
        // TODO: sync this with Envi::getState()
        $node->setAttribute('lang', Envi\Setup::getLocale());
        $node->setAttribute('site', Envi::getSubsite());
        $node->setAttribute('host', $host = Envi::getHost());
        $node->setAttribute('mainhost', $mainhost = Envi::getHost(true));
        $node->setAttribute('instance', $instance ? $instance : View::$instance);
        $node->setAttribute('uri', Envi::getUri());
        $node->setAttribute('controllerUri', Action::getControllerUri());
        if ($host != $mainhost) {
            $node->setAttribute('urlprefix', 'http://' . $mainhost);
        }
        // get user agent
        Envi\UserAgent::getUserAgentXML($node);
        // ajax flag
        $node->setAttribute(
            'ajax',
            (
                Request::isAjax()
                or
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage')
            ) ? '1' : '0'
        );
        $node->setAttribute(
            'switcher',
            (!$this->cache
                and isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage'
            ) ? '1' : '0'
        );
        // build number
        $node->setAttribute('build', Version::getBuild());
        // date
        /** @var $dateNode \DOMElement */
        $dateNode = $node->appendChild($xml->createElement('date'));
        $dateKeys = ['d', 'e', 'A', 'a', 'm', 'B', 'b', 'Y', 'y', 'c', 'x', 'H', 'M', 'S'];
        $dateValues = explode('|', strftime('%' . implode('|%', $dateKeys)));
        $dateCombined = array_combine($dateKeys, $dateValues);
        foreach ($dateCombined as $k => $v) {
            $dateNode->setAttribute($k, $v);
        }
        // debug flag
        $node->setAttribute('debug', Debugger::isEnabled() ? '1' : '0');
        // config values (for js variable)
        $configNode = $node->appendChild($xml->createElement('config'));
        Envi::getStateXML($configNode);
        // menu
        if ($menuResource = Resourcer::getInstance('menu')->compile(View::$instance)) {
            $menuXML = new \DOMDocument();
            $menuXML->loadXML($menuResource);
            $node->appendChild($xml->importNode($menuXML->documentElement, true));
        }
        // auth
        Auth::getInstance()->getAuthXML($node);
        // locale
        Locales::getInstance()->getLocaleXML($node);
        // Add config js object
        $config = Envi::getState();
        $confJS = '';
        foreach ($config as $k => $v) {
            $confJS .= "config.{$k}='" . addslashes($v) . "';";
        }
        $node->setAttribute('jsConfig', $confJS);
        Debugger::addLine('Filling XML data for render: Done');
        Debugger::debugXML($node);
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
}
