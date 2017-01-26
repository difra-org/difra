<?php

namespace Difra\Envi;

use Difra\Plugin;

/**
 * Class Roots
 * @package Difra\Envi
 */
class Roots
{
    const FIRST_FW = 'asc';
    const FIRST_APP = 'desc';

    /** @var string Framework root */
    private $fw = null;
    /** @var string[] Plugin roots */
    private $plugins = [];
    /** @var string Main application root */
    private $main = null;
    /** @var string[] Additional application root */
    private $additional = [];
    /** @var string Selected application root */
    private $application = null;

    /**
     * Get roots list
     * @param bool $order
     * @return string[]
     */
    public static function get($order)
    {
        $directories = null;
        $directoriesReversed = null;
        if ($order == self::FIRST_APP) {
            return $directoriesReversed ?: $directoriesReversed = array_reverse(self::get(self::FIRST_FW));
        }
        if (!is_null($directories)) {
            return $directories;
        }
        $me = self::getInstance();
        return $directories = array_merge(
            [$me->fw],
            $me->plugins,
            self::getUserRoots($order)
        );
    }

    /**
     * Singleton
     * @return Roots
     */
    private static function getInstance()
    {
        static $instance = null;
        return $instance ?: $instance = new self();
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->fw = dirname(__FILE__, 3);
        $this->plugins = Plugin::getPaths();
        $this->main = dirname($this->fw, 3);
//        $this->application = null; // todo
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Set application directory
     * @param $directory
     */
    public static function setApplicationRoot($directory)
    {
        self::getInstance()->application = $directory;
    }

    /**
     * Add additional root directory
     * @param $directory
     */
    public static function addRoot($directory)
    {
        self::getInstance()->additional[] = $directory;
    }

    /**
     * Get user controlled roots (main, application, additional)
     * @param bool $order
     * @return array|null
     */
    public static function getUserRoots($order = self::FIRST_FW)
    {
        $directories = null;
        $directoriesReversed = null;
        if ($order == self::FIRST_APP) {
            return $directoriesReversed ?: $directoriesReversed = array_reverse(self::getUserRoots(self::FIRST_FW));
        }
        if (!is_null($directories)) {
            return $directories;
        }
        $me = self::getInstance();
        return $directories = array_merge(
            [$me->main],
            $me->application ? [$me->application] : [],
            $me->additional
        );
    }

    /**
     * Get project root
     * @return string
     */
    public static function getRoot()
    {
        return self::getInstance()->main;
    }

    /**
     * Get application root
     * @return string
     */
    public static function getApplication()
    {
        return self::getInstance()->application;
    }

    /**
     * Get framework root
     * @return string
     */
    public static function getFW()
    {
        return self::getInstance()->fw;
    }
}
