<?php

namespace Difra;

/**
 * Class Autoloader
 * Class auto loader
 * @package Difra
 */
class Autoloader
{
    /** @var array Class black list */
    private static $bl = ['sqlite3'];
    /** @var string[string] */
    private static $psr4 = null;

    /**
     * Auto loader method
     * @param $class
     * @throws Exception
     */
    public static function load($class)
    {
        if (in_array(strtolower(trim($class, '\\')), self::$bl)) {
            return;
        }
        $file = self::class2file($class);

        /** @noinspection PhpIncludeInspection */
        include_once($file);
    }

    /**
     * Get file name for class and namespace
     * @param string $class
     * @return string
     */
    public static function class2file($class)
    {
        $class = ltrim($class, '\\');
        $parts = explode('\\', $class);

        // framework and plugins
        if ($parts[0] === 'Difra') {
            if (sizeof($parts) > 4 and
                $parts[0] == 'Difra' and $parts[1] == 'Plugins' and $parts[3] == 'Objects'
            ) {
                $plugin = strtolower($parts[2]);
                $parts = array_slice($parts, 4);
                $path = DIR_PLUGINS . "$plugin/objects/";
            } elseif ($parts[0] == 'Difra' and $parts[1] == 'Plugins') {
                $name = strtolower($parts[2]);
                // search for Plugins/Name classes in plugins/name/lib/name.php
                if (sizeof($parts) == 3) {
                    $parts[] = $name;
                }
                $parts = array_slice($parts, 3);
                $path = DIR_PLUGINS . "$name/lib/";
            } else {
                $path = DIR_FW . 'lib/';
                array_shift($parts);
            }
        } else {
            // psr4
            if (!empty(self::$psr4)) {
                $psrFrom = $parts;
                $psrTo = [];
                while (!empty($psrFrom)) {
                    $psrClass = implode('/', $psrFrom);
                    if (isset(self::$psr4[$psrClass])) {
                        return DIR_ROOT . self::$psr4[$psrClass] . '/' . implode('/', $psrTo) . '.php';
                    }
                    array_unshift($psrTo, array_pop($psrFrom));
                }
            }
            // default case
            $path = DIR_ROOT . 'lib/';
        }
        return $path . strtolower(implode('/', $parts)) . '.php';
    }

    /**
     * Set autoloader handler
     * @throws exception
     */
    public static function register()
    {
        spl_autoload_register('Difra\Autoloader::load');
    }

    /**
     * Add class name to blacklist
     * @param string $class
     */
    public static function addBL($class)
    {
        $lClass = strtolower(trim($class, '\\'));
        if (!in_array($lClass, self::$bl)) {
            self::$bl[] = $lClass;
        }
    }

    public static function init()
    {
        self::$psr4 = Config::getInstance()->get('psr4') ?: null;
    }
}
