<?php

namespace Difra\Events;

use Difra\Envi;
use Difra\Exception;

/**
 * Class System
 * @package Difra\Events
 */
class System extends Event
{
    /**
     * System constructor.
     * @param string $name
     * @throws Exception
     */
    protected function __construct($name)
    {
        self::init();
        if (!in_array($name, self::$systemEvents)) {
            throw new Exception('System event access to non-system event is not allowed');
        }
        parent::__construct($name);
    }

    /**
     * Trigger system event prevention
     * @throws Exception
     */
    public function trigger()
    {
        throw new Exception('System event should not be explicitly called');
    }

    /**
     * Register default handler
     * @param callable $handler
     * @throws Exception
     */
    public function registerDefaultHandler(callable $handler)
    {
        throw new Exception('System events default handlers are read only');
    }

    /**
     * Run system events
     */
    public static function run()
    {
        foreach (self::$systemEvents as $eventType) {
            self::getInstance($eventType)->start();
        }
    }

    /**
     * Init system events
     */
    private static function init()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        self::getInstance(Event::EVENT_CORE_INIT)->realRegisterDefaultHandler('Difra\Debugger::init');
        self::getInstance(Event::EVENT_CORE_INIT)->realRegisterDefaultHandler('Difra\Envi\Setup::run');
        self::getInstance(Event::EVENT_CORE_INIT)->realRegisterDefaultHandler('Difra\Envi\Session::init');

        self::getInstance(Event::EVENT_PLUGIN_LOAD)->realRegisterDefaultHandler('\Difra\Plugin::initAll');
        if (Envi::getMode() == 'web') {
            self::getInstance(Event::EVENT_ACTION_SEARCH)->realRegisterDefaultHandler('\Difra\Controller::init');
            self::getInstance(Event::EVENT_ACTION_DISPATCH)->realRegisterDefaultHandler('\Difra\Controller::runDispatch');
            self::getInstance(Event::EVENT_ACTION_RUN)->realRegisterDefaultHandler('\Difra\Controller::run');
            self::getInstance(Event::EVENT_ACTION_ARRIVAL)->realRegisterDefaultHandler('\Difra\Controller::runArrival');
            self::getInstance(Event::EVENT_RENDER_RUN)->realRegisterDefaultHandler('\Difra\View\Output::start');
        }
    }
}
