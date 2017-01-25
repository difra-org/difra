<?php

namespace Difra;

use Difra\Envi\Roots;

/**
 * Class Events
 * @package Difra
 */
class Events
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
    /** @var array */
    private static $types = [
        self::EVENT_CORE_INIT,
        self::EVENT_CONFIG_LOAD,
        self::EVENT_PLUGIN_LOAD,
        self::EVENT_PLUGIN_INIT,

        self::EVENT_ACTION_REDEFINE,
        self::EVENT_ACTION_SEARCH,
        self::EVENT_ACTION_DISPATCH,
        self::EVENT_ACTION_PRE_RUN,
        self::EVENT_ACTION_RUN,
        self::EVENT_ACTION_ARRIVAL,
        self::EVENT_ACTION_DONE,

        self::EVENT_RENDER_INIT,
        self::EVENT_RENDER_RUN,
        self::EVENT_RENDER_DONE
    ];
    /** @var array */
    private static $events = null;

    /**
     * Trigger all events one by one
     */
    public static function run()
    {
        self::init();
        foreach (self::$events as $type => $foo) {
            Debugger::addEventLine('Event ' . $type . ' started');
            self::start($type);
        }

        Debugger::addLine('Done running events');
        if (Envi::getMode() == 'web') {
            Debugger::checkSlow();
        }
    }

    /**
     * Register framework event handlers
     */
    public static function init()
    {
        static $initDone = false;
        if ($initDone) {
            return;
        }
        $initDone = true;

        foreach (self::$types as $type) {
            self::$events[$type] = [];
        }

        self::register(self::EVENT_CORE_INIT, 'Difra\Debugger::init');
        self::register(self::EVENT_CORE_INIT, 'Difra\Envi\Setup::run');
        self::register(self::EVENT_CORE_INIT, '\Difra\Envi\Session::init');
//        self::register(self::EVENT_CORE_INIT, '\Difra\Autoloader::init');

        self::register(self::EVENT_PLUGIN_LOAD, '\Difra\Plugin::initAll');
        if (Envi::getMode() == 'web') {
            self::register(self::EVENT_ACTION_SEARCH, '\Difra\Controller::init');
            self::register(self::EVENT_ACTION_DISPATCH, '\Difra\Controller::runDispatch');
            self::register(self::EVENT_ACTION_RUN, '\Difra\Controller::run');
            self::register(self::EVENT_ACTION_ARRIVAL, '\Difra\Controller::runArrival');
            self::register(self::EVENT_RENDER_RUN, '\Difra\View\Output::start');
        }
        if (!empty($initRoots = Roots::getUserRoots())) {
            foreach ($initRoots as $initRoot) {
                if (file_exists($initPHP = ($initRoot . '/src/init.php'))) {
                    /** @noinspection PhpIncludeInspection */
                    include_once($initPHP);
                }
            }
        }
    }

    /**
     * Register event handler
     * @param string $type Event name
     * @param callable $callback
     * @throws Exception
     */
    public static function register($type, callable $callback)
    {
        self::init();
        if (!in_array($type, self::$types)) {
            throw new Exception('Invalid event type: ' . $type);
        }
        self::$events[$type][] = $callback;
    }

    /**
     * Call registered handlers for an event
     * @param $event
     */
    private static function start($event)
    {
        $handlers = self::$events[$event];
        if (empty($handlers)) {
            return;
        }
        foreach ($handlers as $handler) {
            Debugger::addEventLine(
                "Handler for $event: $handler started"
            );
            call_user_func($handler);
        }
    }
}
