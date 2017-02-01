<?php
use Difra\Envi\Roots;
use Difra\Events\System;

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
        \Difra\Envi::setMode(!empty($_SERVER['REQUEST_METHOD']) ? 'web' : 'cli');
        if (!empty($initRoots = Roots::getUserRoots())) {
            foreach ($initRoots as $initRoot) {
                if (file_exists($initPHP = ($initRoot . '/src/init.php'))) {
                    /** @noinspection PhpIncludeInspection */
                    include_once($initPHP);
                }
            }
        }
        System::run();
    }
}
