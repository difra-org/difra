<?php

namespace Difra;

use Difra\Envi\Roots;

/**
 * Class Events
 * @package Difra
 */
class Events
{
    const EVENT_CORE_INIT = 'core-init';
    const EVENT_CONFIG_LOAD = 'config';
    const EVENT_PLUGIN_LOAD = 'plugins-load';
    const EVENT_PLUGIN_INIT = 'plugins-init';
    const EVENT_ACTION_REDEFINE = 'pre-action';
    const EVENT_ACTION_SEARCH = 'action-find';
    const EVENT_ACTION_DISPATCH = 'action-dispatch';
    const EVENT_ACTION_PRE_RUN = 'init-done';
    const EVENT_ACTION_RUN = 'action-run';
    const EVENT_ACTION_ARRIVAL = 'action-arrival';
    const EVENT_ACTION_DONE = 'dispatch';
    const EVENT_RENDER_INIT = 'render-init';
    const EVENT_RENDER_RUN = 'render-run';
    const EVENT_RENDER_DONE = 'done';
    /** @var array */
    private static $types = [
        self::EVENT_CORE_INIT, // init some classes
        self::EVENT_CONFIG_LOAD, // load configuration
        self::EVENT_PLUGIN_LOAD, // load plugins
        self::EVENT_PLUGIN_INIT, // init plugins

        self::EVENT_ACTION_REDEFINE, // this event lets you define controller and action
        self::EVENT_ACTION_SEARCH, // default controller and action detect
        self::EVENT_ACTION_DISPATCH, // run controller->dispatch()
        self::EVENT_ACTION_PRE_RUN, // event between controller+action detection and action run
        self::EVENT_ACTION_RUN, // run action
        self::EVENT_ACTION_ARRIVAL, // run controller->arrival()
        self::EVENT_ACTION_DONE, // run dispatchers

        self::EVENT_RENDER_INIT, // init view
        self::EVENT_RENDER_RUN, // render view
        self::EVENT_RENDER_DONE // after page render
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

        self::register(self::EVENT_CORE_INIT, 'Difra\\Debugger', 'init');
        self::register(self::EVENT_CORE_INIT, 'Difra\\Envi\\Setup', 'run');
        self::register(self::EVENT_CORE_INIT, 'Difra\\Envi\\Session', 'init');
//        self::register(self::EVENT_CORE_INIT, 'Difra\\Autoloader', 'init');

//        self::register(self::EVENT_PLUGIN_LOAD, 'Difra\\Plugger', 'init');
        self::register(self::EVENT_PLUGIN_INIT, 'Difra\\Plugin', 'initAll');
        if (Envi::getMode() == 'web') {
            self::register(self::EVENT_ACTION_SEARCH, 'Difra\\Controller', 'init');
            self::register(self::EVENT_ACTION_DISPATCH, 'Difra\\Controller', 'runDispatch');
            self::register(self::EVENT_ACTION_RUN, 'Difra\\Controller', 'run');
            self::register(self::EVENT_ACTION_ARRIVAL, 'Difra\\Controller', 'runArrival');
            self::register(self::EVENT_RENDER_RUN, 'Difra\\View\\Output', 'start');
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
     * @param string $class Handler class (should contain getInstance() singleton method)
     * @param bool|string $method Handler method (if false, only getInstance() will be called)
     * @throws Exception
     */
    public static function register($type, $class, $method = false)
    {
        self::init();
        if (!in_array($type, self::$types)) {
            throw new Exception('Invalid event type: ' . $type);
        }
        self::$events[$type][] = [
            'class' => $class,
            'method' => $method
        ];
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
                'Handler for ' . $event . ': ' . $handler['class'] . '->' . ($handler['method']
                    ? $handler['method'] : 'getInstance') . ' started'
            );
            call_user_func([$handler['class'], $handler['method']]);
        }
    }
}
