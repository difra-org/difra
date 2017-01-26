<?php

namespace Difra;

/**
 * Class Plugin
 * @property string|string[] provides
 * @package Difra
 */
abstract class Plugin
{
    // storage
    /** @var self[] Plugins registry */
    static private $plugins = [];
    /** @var string[] Plugin class to name translations */
    static private $classes = [];
    // fields
    /** @var string Plugin name */
    private $name = null;
    /** @var bool Enabled flag */
    private $enabled = false;
    /** @var string Class */
    private $class = null;
    /** @var string Path */
    private $path = null;

    /**
     * Plugin init
     * @return mixed
     */
    abstract protected function init();

    /**
     * Enable plugin
     */
    final public static function enable()
    {
        if (isset(self::$classes[static::class])) {
            return;
        }
        $plugin = new static;
        self::$plugins[$plugin->name] = $plugin;
        self::$classes[$plugin->class] = $plugin;
        $plugin->enabled = true;
    }

    /**
     * Protected constructor.
     */
    final private function __construct()
    {
        // get class
        $this->class = static::class;
        // get path
        $reflection = new \ReflectionClass($this->class);
        $this->path = dirname($reflection->getFileName(), 2);
        // get name
        $chunks = explode('\\', $this->class);
        end($chunks);
        $this->name = prev($chunks);
    }

    /**
     * Get all plugins' paths
     * @return array|null
     */
    public static function getPaths()
    {
        static $paths = null;
        if (!is_null($paths)) {
            return $paths;
        }
        self::initAll();
        $paths = [];
        if (!empty(self::$plugins)) {
            foreach (self::$plugins as $plugin) {
                $paths[] = $plugin->path;
            }
        }
        return $paths;
    }

    /**
     * Init all plugins
     */
    public static function initAll()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (!empty(self::$plugins)) {
            foreach (self::$plugins as $plugin) {
                $plugin->init();
            }
        }
    }

    /**
     * Get Sitemap as an array with following elements:
     * [
     *     'loc' => 'http://example.com/page', // required
     *     'lastmod' => '2005-01-01',
     *     'changefreq' => 'monthly',
     *     'priority' => 0.8
     * ]
     * @return array|bool
     */
    public function getSitemap()
    {
        return false;
    }

    /**
     * Get plugins array
     * @return Plugin[]
     */
    public static function getList()
    {
        return self::$plugins;
    }

    /**
     * Is plugin enabled?
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }
}
