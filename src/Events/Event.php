<?php

namespace Difra\Events;

use Difra\Debugger;
use Difra\Exception;

/**
 * Class Events
 * @package Difra
 */
class Event
{
    /** Init core */
    const EVENT_CORE_INIT = 'core-init';
    /** Load configuration */
    const EVENT_CONFIG_LOAD = 'config';
    /** For plugins system initialization */
    const EVENT_PLUGIN_LOAD = 'plugins-load';
    /** For plugins' early hooks */
    const EVENT_PLUGIN_INIT = 'plugins-init';
    /** For events before action processing */
    const EVENT_ACTION_REDEFINE = 'pre-action';
    /** Search matching action event */
    const EVENT_ACTION_SEARCH = 'action-find';
    /** Run controller->dispatch() */
    const EVENT_ACTION_DISPATCH = 'action-dispatch';
    /** For events before action exec */
    const EVENT_ACTION_PRE_RUN = 'init-done';
    /** Action exec */
    const EVENT_ACTION_RUN = 'action-run';
    /** For events after action exec */
    const EVENT_ACTION_ARRIVAL = 'action-arrival';
    /** For events run last */
    const EVENT_ACTION_DONE = 'dispatch';
    /** Initialize render data */
    const EVENT_RENDER_INIT = 'render-init';
    /** Run render */
    const EVENT_RENDER_RUN = 'render-run';
    /** For events to be run after render */
    const EVENT_RENDER_DONE = 'done';
    /** Run in all modes */
    const RUN_ALL = 'all';
    /** Run in web mode */
    const RUN_WEB = 'web';
    /** @var array */
    protected static $systemEvents = [
        self::EVENT_CORE_INIT => self::RUN_ALL,
        self::EVENT_CONFIG_LOAD => self::RUN_ALL,
        self::EVENT_PLUGIN_LOAD => self::RUN_ALL,
        self::EVENT_PLUGIN_INIT => self::RUN_ALL,

        self::EVENT_ACTION_REDEFINE => self::RUN_WEB,
        self::EVENT_ACTION_SEARCH => self::RUN_WEB,
        self::EVENT_ACTION_DISPATCH => self::RUN_WEB,
        self::EVENT_ACTION_PRE_RUN => self::RUN_WEB,
        self::EVENT_ACTION_RUN => self::RUN_WEB,
        self::EVENT_ACTION_ARRIVAL => self::RUN_WEB,
        self::EVENT_ACTION_DONE => self::RUN_WEB,

        self::EVENT_RENDER_INIT => self::RUN_WEB,
        self::EVENT_RENDER_RUN => self::RUN_WEB,
        self::EVENT_RENDER_DONE => self::RUN_WEB
    ];
    // event object fields
    /** @var string Event name */
    private $name = null;
    /** @var bool Event is running */
    private $running = false;
    /** @var bool Event completed */
    private $completed = false;
    /** @var callable[] Event handlers */
    private $handlers = [];
    /** @var callable[] Default handlers */
    private $defaultHandlers = [];
    /** @var bool preventDefault() called */
    private $preventDefault = false;
    /** @var bool stopPropagation() called */
    private $stopPropagation = false;

    /**
     * Factory
     * @param $name
     * @return mixed|static
     */
    public static function getInstance($name)
    {
        static $instances = [];
        if (isset($instances[$name])) {
            return $instances[$name];
        }
        if (isset(self::$systemEvents[$name])) {
            return $instances[$name] = new System($name);
        } else {
            return $instances[$name] = new Event($name);
        }
    }

    /**
     * Event constructor.
     * @param string $name
     */
    protected function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Trigger custom event
     * @throws Exception
     * @throws \Difra\View\HttpError
     */
    public function trigger()
    {
        $this->start();
    }

    /**
     * Call handlers
     * @throws \Difra\View\HttpError
     * @throws Exception
     */
    protected function start()
    {
        if ($this->running) {
            throw new Exception('Event is already running');
        }
        $this->running = true;
        if (!empty($this->handlers)) {
            $handlers = array_reverse($this->handlers);
            foreach ($handlers as $handler) {
                if ($this->stopPropagation) {
                    break;
                }
                Debugger::addEventLine(
                    is_string($handler)
                        ? "Handler for {$this->name}: $handler started"
                        : (
                    is_array($handler)
                        ? "Handler for {$this->name}: {$handler[0]}::{$handler[1]} started"
                        : "Handler for {$this->name}: [closure] started"
                    )
                );
                call_user_func($handler, $this);
            }
        }
        if (!empty($this->defaultHandlers)) {
            foreach ($this->defaultHandlers as $handler) {
                if ($this->preventDefault or $this->stopPropagation) {
                    break;
                }
                Debugger::addEventLine(
                    is_string($handler)
                        ? "Handler for {$this->name}: $handler started"
                        : (
                    is_array($handler)
                        ? "Handler for {$this->name}: {$handler[0]}::{$handler[1]} started"
                        : "Handler for {$this->name}: [closure] started"
                    )
                );
                call_user_func($handler, $this);
            }
        }
        $this->completed = true;
    }

    /**
     * Register handler
     * @param callable $handler
     */
    public function registerHandler(callable $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Register default handler
     * @param callable $handler
     */
    public function registerDefaultHandler(callable $handler)
    {
        $this->realRegisterDefaultHandler($handler);
    }

    /**
     * Really register default handler
     * @param callable $handler
     */
    protected function realRegisterDefaultHandler(callable $handler)
    {
        $this->defaultHandlers[] = $handler;
    }

    /**
     * Stop event run
     */
    public function stopPropagation()
    {
        $this->stopPropagation = true;
    }

    /**
     * Prevent running default event handlers
     */
    public function preventDefault()
    {
        $this->preventDefault = true;
    }
}
