<?php

declare(strict_types=1);

namespace Difra\Envi;

use Difra\Plugin;

/**
 * Class Roots
 * @package Difra\Envi
 */
class Roots
{
    public const FIRST_FW = 'asc';
    public const FIRST_APP = 'desc';

    /** @var ?string Framework root */
    private ?string $fw;
    /** @var string[] Plugin roots */
    private array $plugins;
    /** @var string|null Main application root */
    private ?string $main;
    /** @var string|null Selected application root */
    private ?string $application = null;
    /** @var string[] Additional application root */
    private array $additional = [];
    /** @var string|null Data directory */
    private ?string $data;

    protected static ?array $directories = null;
    protected static ?array $directoriesRev = null;

    /**
     * Get roots list
     * @param string $order
     * @return string[]
     */
    public static function get(string $order): array
    {
        if ($order === self::FIRST_APP) {
            return self::$directoriesRev ?? self::$directoriesRev = array_reverse(self::get(self::FIRST_FW));
        }
        if (!is_null(self::$directories)) {
            return self::$directories;
        }
        $me = self::getInstance();
        return self::$directories = array_merge(
            [$me->fw],
            $me->plugins,
            self::getUserRoots($order)
        );
    }

    /**
     * Singleton
     * @return Roots
     */
    private static function getInstance(): Roots
    {
        static $instance = null;
        return $instance ?? $instance = new self();
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
        if (!empty($_SERVER['VHOST_DATA'])) {
            $this->data = $_SERVER['VHOST_DATA'];
        } else {
            $this->data = dirname($this->main) . '/data';
        }
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
     * @param string $order
     * @return array
     */
    public static function getUserRoots(string $order = self::FIRST_FW): array
    {
        $directories = null;
        $reversed = null;
        if ($order == self::FIRST_APP) {
            return $reversed ?: array_reverse(self::getUserRoots());
        }
        if (!is_null($directories)) {
            return $directories;
        }
        $me = self::getInstance();
        return array_merge(
            [$me->main],
            $me->additional,
            $me->application ? [$me->application] : []
        );
    }

    /**
     * Get project root
     * @return string
     */
    public static function getRoot(): string
    {
        return self::getInstance()->main;
    }

    /**
     * Get application root
     * @return string
     */
    public static function getApplication(): string
    {
        return self::getInstance()->application;
    }

    /**
     * Get framework root
     * @return string
     */
    public static function getFW(): string
    {
        return self::getInstance()->fw;
    }

    /**
     * Get data dir path
     * @return string
     */
    public static function getData(): string
    {
        return self::getInstance()->data;
    }

    /**
     * Set data dir path
     * @param string $path
     */
    public static function setData(string $path): void
    {
        self::getInstance()->data = $path;
    }
}
