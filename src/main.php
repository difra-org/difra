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
        \Difra\Envi::setMode(!empty($_SERVER['REQUEST_METHOD']) ? 'web' : 'cli');
        \Difra\Events::run();
    }
}
