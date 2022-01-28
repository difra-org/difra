<?php

declare(strict_types=1);

namespace Difra;

use Difra\Envi\Action;
use Difra\Envi\Request;
use Difra\View\HTML\Element\HTML;
use Difra\View\Layout;
use Difra\View\Output;
use JetBrains\PhpStorm\Pure;

/**
 * Abstract controller
 * Class Controller
 * @package Difra
 */
abstract class Controller
{
    /** Default web server-side caching time, seconds */
    public const DEFAULT_CACHE = 60;
    /** @var array URI parts to be used as parameters */
    protected static array $parameters = [];
    /** @var bool */
    public bool $isAjaxAction = false;
    /** @var bool|int Web server-side page caching (false = no, int = seconds, true = DEFAULT_CACHE) */
    public int|bool $cache = false;
    /** @var string|null */
    protected ?string $method = null;
    /**
     * View/Output links
     */
    /** @var ?string */
    public ?string $output = null;
    /** @var string */
    public string $outputType = 'text/plain';
    /**
     * View/Layout links
     */
    /** @var ?\DOMDocument */
    public ?\DOMDocument $xml;
    /** @var ?\DOMElement */
    public ?\DOMElement $realRoot;
    /** @var ?\DOMElement Root */
    public ?\DOMElement $root;
    /** @var ?\DOMElement */
    public ?\DOMElement $header;
    /** @var ?\DOMElement */
    public ?\DOMElement $footer;
    /** @var ?\Difra\View\HTML\Element\HTML */
    public ?HTML $html = null;

    /**
     * Constructor
     * @param array $parameters Parameters from url (from \Difra\Envi\Action)
     */
    final public function __construct(array $parameters = [])
    {
        self::$parameters = $parameters;

        Layout::getInstance()->linkController($this);
        $this->output =& Output::$output;
        $this->outputType =& Output::$outputType;
    }

    /**
     * Controller dispatcher
     * Executed before action call.
     */
    public function dispatch(): void
    {
    }

    /**
     * Controller arrival
     * Executed after action call.
     */
    public function arrival(): void
    {
    }

    /**
     * Pre-init
     * Needed for skipping xml fill on error pages
     * @throws \Difra\Exception
     */
    final public static function init(): void
    {
        self::getInstance();
    }

    /**
     * Call action factory
     * @return static
     * @throws \Difra\Exception
     */
    public static function getInstance(): static
    {
        static $instance = null;
        return $instance ?? $instance = Action::getController();
    }

    /**
     * Run dispatch()
     * @throws \Difra\Exception
     */
    public static function runDispatch()
    {
        Debugger::addLine('Started Controller->dispatch()');
        self::getInstance()->dispatch();
        Debugger::addLine('Finished controller->dispatch()');
    }

    /**
     * Run suitable action
     * @throws \Difra\Exception|\Difra\View\HttpError|\ReflectionException
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
     * Run arrival()
     * @throws \Difra\Exception
     */
    public static function runArrival()
    {
        Debugger::addLine('Started Controller->arrival()');
        self::getInstance()->arrival();
        Debugger::addLine('Finished Controller->arrival()');
    }

    /**
     * Choose action
     * @throws \Difra\View\HttpError
     */
    private function chooseAction(): void
    {
        if (Request::isAjax() and Action::$methodAjaxAuth and Auth::getInstance()->isAuthorized()) {
            $this->isAjaxAction = true;
            $this->method = 'methodAjaxAuth';
        } elseif (Request::isAjax() and Action::$methodAjax) {
            $this->isAjaxAction = true;
            $this->method = 'methodAjax';
        } elseif (Action::$methodAuth and Auth::getInstance()->isAuthorized()) {
            $this->method = 'methodAuth';
        } elseif (Action::$method) {
            $this->method = 'method';
        } elseif (Request::isAjax() and Action::$methodAjaxAuth) {
            self::$parameters = [];
            throw new View\HttpError(401);
        } elseif (Action::$methodAuth) {
            self::$parameters = [];
            throw new View\HttpError(401);
        } else {
            throw new View\HttpError(404);
        }
    }

    /**
     * Process parameters and run action
     * @throws \ReflectionException|\Difra\View\HttpError
     */
    private function callAction(): void
    {
        $method = $this->method;
        /** @noinspection PhpVariableVariableInspection */
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
            $class = $parameter->getType()?->getName() ?? 'Difra\Param\NamedString';
            if (call_user_func([$class, 'getSource']) == 'query' and call_user_func([$class, 'isNamed'])) {
                $namedParameters[] = $parameter->getName();
            }
        }

        // get parameter values
        $callParameters = [];
        foreach ($actionParameters as $parameter) {
            $name = $parameter->getName();
            $class = $parameter->getType()?->getName() ?? 'Difra\Param\NamedString';
            switch (call_user_func([$class, 'getSource'])) {
                // query parameters
                case 'query':
                    if (call_user_func([$class, 'isNamed'])) {
                        // named parameter
                        if (sizeof(self::$parameters) >= 2 and self::$parameters[0] == $name) {
                            array_shift(self::$parameters);
                            if (!call_user_func([$class, 'verify'], self::$parameters[0])) {
                                throw new View\HttpError(404);
                            }
                            $callParameters[$parameter->getName()] =
                                new $class(array_shift(self::$parameters));
                        } elseif (call_user_func(["$class", 'isAuto'])) {
                            $callParameters[$name] = new $class();
                        } elseif (!$parameter->isOptional()) {
                            throw new View\HttpError(404);
                        } else {
                            $callParameters[$parameter->getName()] = null;
                        }
                        array_shift($namedParameters);
                    } elseif (!empty(self::$parameters) and (!$parameter->isOptional() or empty($namedParameters) or
                            self::$parameters[0] != $namedParameters[0])
                    ) {
                        if (!call_user_func([$class, 'verify'], self::$parameters[0])) {
                            throw new View\HttpError(404);
                        }
                        $callParameters[$name] = new $class(array_shift(self::$parameters));
                    } elseif (!$parameter->isOptional()) {
                        throw new View\HttpError(404);
                    } else {
                        $callParameters[$parameter->getName()] = null;
                    }
                    break;
                // ajax parameters
                case 'ajax':
                    $value = Request::getParam($name);
                    if (!is_null($value) and $value !== '') {
                        if (!call_user_func([$class, 'verify'], $value)) {
                            Ajaxer::invalid($name);
                            continue 2;
                        }
                        $callParameters[$name] = new $class($value);
                    } elseif (call_user_func([$class, 'isAuto'])) {
                        $callParameters[$name] = new $class();
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
    public function putExpires(bool|int|null $ttl = null): void
    {
        if (Debugger::isEnabled()) {
            return;
        }
        if (is_null($ttl)) {
            $ttl = $this->cache;
        } elseif ($ttl === true) {
            $ttl = self::DEFAULT_CACHE;
        }
        if ($ttl >= 0) {
            View::addExpires($ttl);
        }
    }

    /**
     * Check referer to prevent cross-site calls
     * Should be called manually
     * @throws Exception
     */
    public function checkReferer(): void
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            throw new Exception('Bad referer');
        }
        /** @noinspection HttpUrlsUsage */
        if ((!str_starts_with($_SERVER['HTTP_REFERER'], 'http://')) && (!str_starts_with($_SERVER['HTTP_REFERER'], 'https://'))
        ) {
            throw new Exception('Bad referer');
        }
        $domain = explode('://', $_SERVER['HTTP_REFERER'], 2);
        $domain = explode('/', $domain[1]);
        $domain = $domain[0] . '/';
        if (!str_contains($domain, Envi::getHost(true))) {
            throw new Exception('Bad referer');
        }
    }

    /**
     * Are unused parameters still left?
     * @return bool
     */
    public static function hasUnusedParameters(): bool
    {
        return !empty(self::$parameters);
    }

    /**
     * Get controller URI shortcut
     */
    #[Pure]
    protected function getUri(): ?string
    {
        return Action::getControllerUri();
    }

    /**
     * Set page title
     * @param string $title
     */
    protected function setTitle(string $title)
    {
        $this->root->setAttribute('pageTitle', $title);
    }

    /**
     * Get page title
     * @return string
     */
    protected function getTitle(): string
    {
        return $this->root->getAttribute('pageTitle');
    }

    /**
     * Set HTML description
     * @param string $description
     */
    protected function setDescription(string $description)
    {
        $this->root->setAttribute('description', $description);
    }

    /**
     * Get HTML description
     * @return string
     */
    protected function getDescription(): string
    {
        return $this->root->getAttribute('description');
    }

    /**
     * Set HTML keywords
     * @param string $keywords
     */
    protected function setKeywords(string $keywords)
    {
        $this->root->setAttribute('keywords', $keywords);
    }

    /**
     * Get HTML keywords
     * @return string
     */
    protected function getKeywords(): string
    {
        return $this->root->getAttribute('keywords');
    }
}
