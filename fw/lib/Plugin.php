<?php

namespace Difra;

use Difra\Unify;

/**
 * Class Plugin
 * @property string|string[] provides
 * @package Difra
 */
abstract class Plugin
{
    /** @var int Matching framework version */
    protected $version = 0;
    /** @var string Description */
    protected $description = '';
    /** @var null|string|array Dependency list */
    protected $require = null;
    private $class;
    private $name = null;
    private $enabled = false;
    private $path = '';

    /**
     * Constructor
     */
    final public function __construct()
    {
        $this->class = get_class($this);
    }

    /**
     * Singleton
     * @return self
     */
    public static function getInstance()
    {
        static $_self = [];
        $class = get_called_class();
        return !empty($_self[$class]) ? $_self[$class] : $_self[$class] = new $class;
    }

    /**
     * Get list of dependencies and other stuff
     * @return array|null
     */
    public function getInfo()
    {
        $info = [];
        // requires
        if (!property_exists($this, 'require') or !$this->require) {
            $info['requires'] = [];
        } elseif (!is_array($this->require)) {
            $info['requires'] = [$this->require];
        } else {
            $info['requires'] = $this->require;
        }
        // provides
        if (!property_exists($this, 'provides') or !$this->provides) {
            $info['provides'] = [];
        } elseif (!is_array($this->provides)) {
            $info['provides'] = [$this->provides];
        } else {
            $info['provides'] = $this->provides;
        }
        // version
        if (!property_exists($this, 'version') or !$this->version) {
            $info['version'] = 0;
        } else {
            $info['version'] = (float)$this->version;
        }
        $info['description'] = property_exists($this, 'description') ? $this->description : '';
        return $info;
    }

    abstract public function init();

    /**
     * Detect if plugin is enabled
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Enable plugin
     * @return bool
     */
    public function enable()
    {
        if ($this->enabled) {
            return false;
        }
        $this->enabled = true;
        Unify::registerObjects($this->getObjects());
        return true;
    }

    /**
     * Get objects provided by plugin
     * @return null
     */
    public function getObjects()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return property_exists($this, 'objects') ? $this->objects : null;
    }

    /**
     * Get plugin directory path
     * @return string
     */
    public function getPath()
    {
        if (!$this->path) {
            $reflection = new \ReflectionClass($this);
            $this->path = dirname($reflection->getFileName());
        }
        return $this->path;
    }

    /**
     * Get plugin name
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $this->name = basename(dirname(str_replace('\\', '/', $this->class)));
        }
        return $this->name;
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
}
