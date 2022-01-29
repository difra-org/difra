<?php

declare(strict_types=1);

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
    private static array $plugins = [];
    /** @var string[] Plugin class to name translations */
    private static array $classes = [];
    // fields
    /** @var string|null Plugin name */
    private ?string $name;
    /** @var bool Enabled flag */
    private bool $enabled = false;
    /** @var string|null Class */
    private ?string $class;
    /** @var string|null Path */
    private ?string $path;

    /**
     * Plugin init
     */
    abstract protected function init(): void;

    /**
     * Enable plugin
     */
    final public static function enable()
    {
        if (isset(self::$classes[static::class])) {
            return;
        }
        $plugin = new static();
        self::$plugins[$plugin->name] = $plugin;
        self::$classes[$plugin->class] = $plugin;
        $plugin->enabled = true;
    }

    /**
     * Protected constructor.
     * @throws \ReflectionException
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
     * @return array
     */
    public static function getPaths(): array
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
     * Get plugins array
     * @return Plugin[]
     */
    public static function getList(): array
    {
        return self::$plugins;
    }

    /**
     * Is plugin enabled?
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get plugin name
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
