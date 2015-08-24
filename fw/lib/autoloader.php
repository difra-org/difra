<?php

namespace Difra;

/**
 * Class Autoloader
 * Class auto loader
 *
 * @package Difra
 */
class Autoloader
{
    /** @var array Class black list */
    private static $bl = ['sqlite3'];
    private static $loader = null;

    /**
     * Auto loader method
     *
     * @param $class
     * @throws Exception
     */
    public static function load($class)
    {
        if (in_array(strtolower(trim($class, '\\')), self::$bl)) {
            return;
        }
        $file = self::class2file($class);

        /**
         * This code is disabled because file_exists() is slow!
         * Do not enable it unless you're not going to use profiler.
         * TODO: consider adding profiler constant
         * if( Debugger::isConsoleEnabled() == Debugger::CONSOLE_ENABLED ) {
         * if( !file_exists( $file ) ) {
         * throw new Exception( 'File "' . $file . "' for class '" . $class . '" was not found.' );
         * }
         * }
         */

        /** @noinspection PhpIncludeInspection */
        @include_once($file);
    }

    /**
     * Get file name for class and namespace
     *
     * @param string $class
     * @return string
     */
    public static function class2file($class)
    {
        $class = ltrim($class, '\\');
        $parts = explode('\\', $class);
        if ($parts[0] != 'Difra') {
            $path = DIR_ROOT . 'lib/';
        } elseif (sizeof($parts) > 4 and $parts[0] == 'Difra' and $parts[1] == 'Plugins' and $parts[3] == 'Objects') {
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
        return $path . strtolower(implode('/', $parts)) . '.php';
    }

    /**
     * Set autoloader handler
     *
     * @throws exception
     */
    public static function register()
    {
        spl_autoload_register('Difra\Autoloader::load');
    }

    /**
     * Add class name to blacklist
     *
     * @param string $class
     */
    public static function addBL($class)
    {
        $lClass = strtolower(trim($class, '\\'));
        if (!in_array($lClass, self::$bl)) {
            self::$bl[] = $lClass;
        }
    }

    public static function setLoader($obj)
    {
        self::$loader = $obj;
    }
}

// Register auto loader class
// TODO: move it somewhere, class + code in same file is something bad
Autoloader::register();
