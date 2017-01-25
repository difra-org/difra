<?php

namespace Difra\Envi;

use Difra\Plugin;

/**
 * Class Roots
 * @package Difra\Envi
 */
class Roots
{
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
     * @param bool $reverse
     * @return array|null
     */
    public static function get($reverse = false)
    {
        $directories = null;
        $directoriesReversed = null;
        if ($reverse) {
            return $directoriesReversed ?: $directoriesReversed = array_reverse(self::get());
        }
        if (!is_null($directories)) {
            return $directories;
        }
        $me = self::getInstance();
        return $directories = array_merge(
            ['fw' => $me->fw],
            $me->plugins,
            self::getUserRoots($reverse)
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
     * @param bool $reverse
     * @return array|null
     */
    public static function getUserRoots($reverse = false)
    {
        $directories = null;
        $directoriesReversed = null;
        if ($reverse) {
            return $directoriesReversed ?: $directoriesReversed = array_reverse(self::get());
        }
        if (!is_null($directories)) {
            return $directories;
        }
        $me = self::getInstance();
        return $directories = array_merge(
            ['main' => $me->main],
            $me->application ? [$me->application] : [],
            $me->additional
        );
    }
}
