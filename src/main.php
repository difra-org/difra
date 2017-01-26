<?php

/**
 * Class Difra
 * @package Difra
 */
class Difra
{
    /**
     * Entry point
     */
    public static function main()
    {
//        // Define path constants for running inside of PHAR
//        if (isset($_)) {
//            define('DIR_ROOT', dirname($_) . '/');
//            define('DIR_PHAR', dirname(dirname(__DIR__)) . '/');
//            define('DIR_DIFRA', DIR_PHAR);
//        }
//        if (!defined('DIR_ROOT')) {
//            if (!empty($_SERVER['VHOST_ROOT'])) {
//                /** @noinspection PhpConstantReassignmentInspection */
//                define('DIR_ROOT', rtrim($_SERVER['VHOST_ROOT'], '/') . '/');
//            } else {
//                /** @noinspection PhpConstantReassignmentInspection */
//                define('DIR_ROOT', dirname(dirname(__DIR__)) . '/');
//            }
//        }
//        if (!defined('DIR_DIFRA')) {
//            if (!empty($_SERVER['VHOST_DIFRA'])) {
//                /** @noinspection PhpConstantReassignmentInspection */
//                define('DIR_DIFRA', rtrim($_SERVER['VHOST_DIFRA'], '/') . '/');
//            } else {
//                /** @noinspection PhpConstantReassignmentInspection */
//                define('DIR_DIFRA', DIR_ROOT);
//            }
//        }
//        define('DIR_FW', DIR_DIFRA . 'fw/');
//        define('DIR_PLUGINS', DIR_DIFRA . 'plugins/');
//        require_once(DIR_FW . 'lib/Envi.php');
//        define('DIR_SITE', DIR_ROOT . 'sites/' . \Difra\Envi::getSubsite() . '/');
//        if (!defined('DIR_DATA')) {
//            define('DIR_DATA', !empty($_SERVER['VHOST_DATA']) ? $_SERVER['VHOST_DATA'] . '/' : DIR_ROOT . 'data/');
//        }
//
//        require_once(DIR_FW . 'lib/Autoloader.php');
//        \Difra\Autoloader::register();

        \Difra\Envi::setMode(!empty($_SERVER['REQUEST_METHOD']) ? 'web' : 'cli');
        \Difra\Events::run();
    }
}