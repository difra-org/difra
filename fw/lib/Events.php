<?php

namespace Difra;

/**
 * Class Events
 * @package Difra
 */
class Events
{
    /** @var array */
    private static $types = [
        'core-init', // init some classes
        'plugins-load', // load plugins
        'config', // load configuration
        'plugins-init', // init plugins

        'pre-action', // this event lets you define controller and action
        'action-find', // default controller and action detect
        'init-done', // event between controller+action detection and action run

        'action-run', // run action

        'dispatch', // run dispatchers

        'render-init', // init view
        'render-run', // render view

        'done' // after page render
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

        self::register('core-init', 'Difra\\Debugger', 'init');
        self::register('core-init', 'Difra\\Envi\\Setup', 'run');
        self::register('core-init', 'Difra\\Envi\\Session', 'init');
        self::register('core-init', 'Difra\\Autoloader', 'init');
        self::register('plugins-load', 'Difra\\Plugger', 'init');
        if (Envi::getMode() == 'web') {
            self::register('action-find', 'Difra\\Controller', 'init');
            self::register('action-run', 'Difra\\Controller', 'run');
            self::register('render-run', 'Difra\\View\\Output', 'start');
        }
        if (file_exists($initPHP = (DIR_ROOT . '/lib/init.php'))) {
            /** @noinspection PhpIncludeInspection */
            include_once($initPHP);
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
