<?php

namespace Difra\Resourcer\Abstracts;

use Difra\Cache;
use Difra\Config;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi\Version;
use Difra\Exception;
use Difra\Plugger;
use Difra\View;

/**
 * Abstract resourcer class
 * Class Common
 *
 * @package Difra\Resourcer\Abstracts
 */
abstract class Common
{
    protected $type = null;
    protected $printable = false;
    protected $contentType = null;

    /**
     * Resource processor
     *
     * @param string $instance
     * @return mixed
     */
    abstract protected function processData($instance);

    protected $resources = [];
    const CACHE_TTL = 86400;

    /**
     * Singleton
     *
     * @return self
     */
    static public function getInstance()
    {
        static $_instances = [];
        $name = get_called_class();
        return isset($_instances[$name]) ? $_instances[$name] : $_instances[$name] = new $name();
    }

    /**
     * Validate instance name
     *
     * @param $instance
     * @return bool
     * @throws Exception
     */
    private function checkInstance($instance)
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $instance)) {
            throw new Exception("Bad Resourcer instance name: '$instance'");
        }
        return true;
    }

    /**
     * Output resource
     *
     * @param $instance
     * @return bool
     * @throws Exception
     */
    public function view($instance)
    {
        if (!$this->isPrintable()) {
            throw new Exception("Resource of type '{$this->type}' is not printable");
        }
        // Cut extension
        $parts = explode('.', $instance);
        if (sizeof($parts) == 2) {
            if ($parts[1] == $this->type) {
                $instance = $parts[0];
            }
        }
        if (!$instance or !$this->checkInstance($instance)) {
            return false;
        }

        /*
         * Disabled due to nginx doesn't support Vary in fastcgi_cache implementation at the moment
         *
        // Detect if browser supports gzip compression
        $enc = false;
        if( !empty( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ) {
            $encTypes = $_SERVER['HTTP_ACCEPT_ENCODING'];
            if( strpos( $encTypes, ',' ) ) {
                $encTypes = explode( ',', $encTypes );
            } else {
                $encTypes = array( $encTypes );
            }
            foreach( $encTypes as $type ) {
                $type = trim( $type );
                switch( $type ) {
                case 'gzip':
                    $enc = 'gzip';
                    break 2;
                }
            }
        }
        */
        $enc = 'gzip';

        if ($enc == 'gzip' and $data = $this->compileGZ($instance)) {
            // header( 'Vary: Accept-Encoding' );
            header('Content-Encoding: gzip');
        } else {
            $data = $this->compile($instance);
        }
        if (!$data) {
            return false;
        }
        header('Content-Type: ' . $this->contentType);
        if (!$modified = Cache::getInstance()->get("{$instance}_{$this->type}_modified")) {
            $modified = gmdate('D, d M Y H:i:s') . ' GMT';
        }
        View::addExpires(Controller::DEFAULT_CACHE);
        header('Last-Modified: ' . $modified);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + self::CACHE_TTL) . ' GMT');
        echo $data;
        return true;
    }

    /**
     * Is resource suitable for direct output?
     *
     * @return bool
     */
    public function isPrintable()
    {
        return $this->printable;
    }

    /**
     * Create gz version for resource
     *
     * @param $instance
     * @return string
     */
    public function compileGZ($instance)
    {
        $cache = Cache::getInstance();
        if ($cache->adapter == Cache::INST_NONE) {
            return false;
        }

        $cacheKey = "{$instance}_{$this->type}";
        if ($cached = $cache->get($cacheKey . '_gz')) {
            if ($cache->get($cacheKey . '_gz_build') == Version::getBuild()) {
                return $cached;
            }
        }

        // wait for updated data, try to get lock
        $busyKey = "{$cacheKey}_gz_busy";
        $busyValue = rand(100000, 999999);
        while (true) {
            if (!$currentBusy = $cache->get($busyKey)) {
                // got updated data?
                if ($cached = $cache->get($cacheKey . '_gz') and
                    $cache->get($cacheKey . '_gz_build') == Version::getBuild()
                ) {
                    return $cached;
                }
                // try to get lock
                $cache->put($busyKey, $busyValue, 7);
                usleep(5000);
            } else {
                // got lock?
                if ($currentBusy == $busyValue) {
                    break;
                }
                usleep(19111);
            }
        }
        // got lock, put data to cache
        $cache->put($cacheKey . '_gz', $data = gzencode($this->compile($instance), 9), self::CACHE_TTL);
        $cache->put($cacheKey . '_gz_build', Version::getBuild(), self::CACHE_TTL);
        $cache->put($cacheKey . '_gz_modified', gmdate('D, d M Y H:i:s') . ' GMT', self::CACHE_TTL);
        // unlock
        $cache->remove($busyKey);
        return $data;
    }

    /**
     * Get compiled resource
     *
     * @param      $instance
     * @param bool $withSources
     * @return bool|null
     */
    public function compile($instance, $withSources = false)
    {
        if (!$this->checkInstance($instance)) {
            return false;
        }

        // get compiled from cache if available
        $cache = Cache::getInstance();

        if ($cache->adapter != Cache::INST_NONE) {
            $cacheKey = "{$instance}_{$this->type}";
            if (!is_null($cached = $cache->get($cacheKey))) {
                if ($cache->get($cacheKey . '_build') == Version::getBuild()) {
                    return $cached;
                }
            }

            // wait for updated data, try to get lock
            $busyKey = "{$cacheKey}_busy";
            $busyValue = rand(100000, 999999);
            while (true) {
                if (!$currentBusy = $cache->get($busyKey)) {
                    // got updated data?
                    if (!is_null($cached = $cache->get($cacheKey)) and
                        $cache->get($cacheKey . '_build') == Version::getBuild()
                    ) {
                        return $cached;
                    }

                    // try to lock cache
                    $cache->put($busyKey, $busyValue, 7);
                    usleep(5000);
                } else {
                    // is cache locked by me?
                    if ($currentBusy == $busyValue) {
                        break;
                    }

                    usleep(19111);
                }
            }

            // compile resource
            $resource = $this->realCompile($instance, $withSources);

            // cache data
            $cache->put($cacheKey, $resource, self::CACHE_TTL);
            $cache->put($cacheKey . '_build', Version::getBuild(), self::CACHE_TTL);
            $cache->put($cacheKey . '_modified', gmdate('D, d M Y H:i:s') . ' GMT', self::CACHE_TTL);

            // unlock cache
            $cache->remove($busyKey);

            return $resource;
        } else {
            return $this->realCompile($instance, $withSources);
        }
    }

    /**
     * Compile resource
     *
     * @param string $instance
     * @param bool   $withSources
     * @throws Exception
     * @return string
     */
    private function realCompile($instance, $withSources = false)
    {
        Debugger::addLine("Resource {$this->type}/{$instance} compile started");
        $res = false;
        if ($this->find($instance)) {
            $this->processDirs($instance);
            $res = $this->processData($instance, $withSources);
        }
        $res = $this->processText($res);
        Debugger::addLine("Resource {$this->type}/{$instance} compile finished");
        return $res;
    }

    /**
     * Search for resource directories
     *
     * @param string $instance
     * @return bool
     */
    private function find($instance)
    {
        // TODO: cache $found and $parents by $type and $instance

        static $paths = null;
        if (is_null($paths)) {
            $paths = Plugger::getPaths();
            $paths = array_merge(
                [
                    DIR_SITE,
                    DIR_ROOT
                ],
                $paths,
                [
                    DIR_FW
                ]
            );
        }

        $found = false;
        $parents = [];
        if (!empty($paths)) {
            foreach ($paths as $dir) {
                if (is_dir($d = "{$dir}{$this->type}/{$instance}")) {
                    $found = true;
                    $parents[] = $d;
                }
                if ($this->withAll($instance) and is_dir($d = "{$dir}{$this->type}/all")) {
                    $found = true;
                    $parents[] = $d;
                }
            }
        }

        if (!$found) {
            return false;
        }
        $this->addDirs($instance, $parents);
        return true;
    }

    /**
     * Find all possible instances for selected resource
     * Warning: this is slow! Do not use it except for administrator area or cron scripts etc.
     *
     * @return array|bool
     */
    public function findInstances()
    {
        $parents = [
            DIR_FW . $this->type,
            DIR_ROOT . $this->type,
            DIR_SITE . $this->type,
        ];
        $paths = Plugger::getPaths();
        if (!empty($paths)) {
            foreach ($paths as $dir) {
                $parents[] = "{$dir}/{$this->type}";
            }
        }

        if (empty($parents)) {
            return false;
        }
        $instances = [];
        foreach ($parents as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $dir = opendir($path);
            while (false !== ($subdir = readdir($dir))) {
                if ($subdir{0} != '.' and is_dir($path . '/' . $subdir)) {
                    $instances[$subdir] = 1;
                }
            }
        }
        return array_keys($instances);
    }

    /**
     * Add directories to search list
     *
     * @param string       $instance
     * @param string|array $dirs
     */
    private function addDirs($instance, $dirs)
    {
        if (!is_array($dirs)) {
            $dirs = [$dirs];
        }

        if (!isset($this->resources[$instance])) {
            $this->resources[$instance] = [];
        }
        if (!isset($this->resources[$instance]['dirs'])) {
            $this->resources[$instance]['dirs'] = [];
        }
        $this->resources[$instance]['dirs'] = array_merge($this->resources[$instance]['dirs'], $dirs);
    }

    /**
     * Search resources by directories
     *
     * @param $instance
     */
    public function processDirs($instance)
    {
        if (empty($this->resources[$instance]['dirs'])) {
            return;
        }
        foreach ($this->resources[$instance]['dirs'] as $dir) {
            $dirHandler = opendir($dir);
            while ($dirEntry = readdir($dirHandler)) {
                if ($dirEntry{0} == '.') {
                    continue;
                }
                $entry = "$dir/$dirEntry";
                if (is_dir($entry)) { // "special"
                    $exp = explode('-', $dirEntry);
                    $special = [
                        'name'    => (sizeof($exp) == 2 ? $exp[0] : $dirEntry),
                        'version' => (sizeof($exp) == 2 ? $exp[1] : 0),
                        'files'   => []
                    ];
                    if (isset($this->resources[$instance]['specials'][$special['name']])) {
                        if ($this->resources[$instance]['specials'][$special['name']]['version'] >
                            $special['version']
                        ) {
                            continue;
                        } else {
                            unset($this->resources[$instance]['specials'][$special['name']]);
                        }
                    }
                    $specHandler = opendir($entry);
                    while ($specSub = readdir($specHandler)) {
                        if ($specSub{0} == '.') {
                            continue;
                        }
                        if (is_file("$entry/$specSub")) {
                            $name = str_replace('.min.', '.', $specSub);
                            $type = ($name == $specSub) ? 'raw' : 'min';
                            if (!isset($special['files'][$name])) {
                                $special['files'][$name] = [];
                            }
                            $special['files'][$name][$type] = "$entry/$specSub";
                        }
                    }
                    $this->resources[$instance]['specials'][$special['name']] = $special;
                } elseif (is_file($entry)) { // "file"
                    if (!isset($this->resources[$instance]['files'])) {
                        $this->resources[$instance]['files'] = [];
                    }
                    $name = str_replace('.min.', '.', $entry);
                    $type = ($name == $entry) ? 'raw' : 'min';
                    if (!isset($special['files'][$name])) {
                        $special['files'][$name] = [];
                    }
                    $this->resources[$instance]['files'][$name][$type] = $entry;
                }
            }
        }
    }

    /**
     * Get list of all matching files
     *
     * @param string $instance
     * @return string[]
     */
    public function getFiles($instance)
    {
        $files = [];
        if (!empty($this->resources[$instance]['specials'])) {
            foreach ($this->resources[$instance]['specials'] as $resource) {
                if (!empty($resource['files'])) {
                    $files = array_merge($files, $resource['files']);
                }
            }
        }
        if (!empty($this->resources[$instance]['files'])) {
            $files = array_merge($files, $this->resources[$instance]['files']);
        }
        return $files;
    }

    /**
     * Should 'all' directories be included?
     *
     * @param string $instance
     * @return bool
     */
    private function withAll($instance)
    {
        $instances = Config::getInstance()->get('instances');
        return
            isset($instances[$instance]) and
            isset($instances[$instance]['withAll']) and
            $instances[$instance]['withAll'];
    }

    /**
     * Resource postprocessing
     *
     * @param string $text
     * @return string
     */
    public function processText($text)
    {
        return $text;
    }
}
