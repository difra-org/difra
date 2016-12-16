<?php

namespace Difra;

/**
 * Class Main
 * @package Difra
 */
class Main
{
    /** Load priority weight: Difra (lowest) */
    const WEIGHT_DIFRA = 10;
    /** Load priority weight: Plugin (low) */
    const WEIGHT_PLUGIN = 20;
    /** Load priority weight: Root (medium) */
    const WEIGHT_ROOT = 30;
    /** Load priority weight: Application (high) */
    const WEIGHT_APP = 40;
    /** Load priority weight: Other (highest) */
    const WEIGHT_OTHER = 50;

    /**
     * Resource roots
     * @var array
     */
    private static $roots = [
        self::WEIGHT_DIFRA => [],
        self::WEIGHT_PLUGIN => [],
        self::WEIGHT_ROOT => [],
        self::WEIGHT_APP => [],
        self::WEIGHT_OTHER => []
    ];

    /** @var string Stored files directory */
    private static $dataRoot = null;

    /**
     * Plugin directories list
     * @var array
     */
    private static $plugins = [];

    /**
     * Init
     */
    public static function init()
    {
        self::preInit();
    }

    /**
     * Launch site
     */
    public static function run()
    {
        self::preInit();
        self::init();
    }

    private function preInit()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        // difra directory
        self::$roots[] =
            $_SERVER['DIR_DIFRA'] // defined by web server
            ?? (defined('DIR_DIFRA') ? DIR_DIFRA : null) // defined by bootstrap
               ?? dirname(__DIR__); // parent directory
        // project root directory
        self::$roots[] = $root =
            $_SERVER['DIR_ROOT'] // defined by web server
            ?? (defined('DIR_ROOT') ? DIR_ROOT : null) // defined by bootstrap
               ?? dirname(dirname(dirname(dirname(__DIR__)))); // try to guess (composer layout)
        // application directory
        self::$roots[] =
            $_SERVER['DIR_SITE'] // defined by web server
            ?? (defined('DIR_SITE') ? DIR_SITE : null) // defined by bootstrap
               ?? $root . '/app/' . Envi::getSubsite(); // trying to guess
        self::$dataRoot =
            $_SERVER['DIR_DATA']
            ?? (defined('DIR_DATA') ? DIR_DATA : null)
               ?? dirname($root) . '/data';
    }

    /**
     * Add root directory
     * @param string $dir Resource root
     * @param int $weight Load order weight
     */
    public static function addRoot($dir, $weight = self::WEIGHT_OTHER)
    {
        self::preInit();
        isset(self::$roots[$weight]) ?: self::$roots[$weight] = [];
        self::$roots[] = $dir;
    }

    /**
     * Add plugin
     * @param Plugin $plugin
     */
    public static function addPlugin(Plugin $plugin)
    {
        self::preInit();
        self::$plugins[] = $plugin;
    }
}
