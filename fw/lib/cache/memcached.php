<?php

namespace Difra\Cache;

use Difra\Cache;
use Difra\Exception;

/**
 * Memcached (memcached module) adapter
 * Class MemCached
 *
 * @package Difra\Cache
 */
class MemCached extends Common
{
    /** @var \Memcached */
    private static $_memcache = null;
    private static $_serialize = true;
    private static $_lifetime = 0;
    public $adapter = Cache::INST_MEMCACHED;

    /**
     * Detect if backend is available
     *
     * @return bool
     */
    public static function isAvailable()
    {
        try {
            if (!extension_loaded('memcached')) {
                return false;
            }
            if (self::$_memcache) {
                return true;
            }

            self::$_memcache = new \MemCached;
            $currentServers = self::$_memcache->getServerList();
            if (empty($currentServers)) {
                return false;
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Get cache record implementation
     *
     * @param string $id
     * @param bool   $doNotTestCacheValidity
     * @return mixed|null
     */
    public function realGet($id, $doNotTestCacheValidity = false)
    {
        $data = @self::$_memcache->get($id);
        return self::$_serialize ? @unserialize($data) : $data;
    }

    /**
     * Test if cache record exists implementation
     *
     * @param string $id
     * @return bool
     */
    public function test($id)
    {
        $data = $this->get($id);
        return !empty($data);
    }

    /**
     * Put cache record implementation
     *
     * @param string $id
     * @param mixed  $data
     * @param bool   $specificLifetime
     * @return bool
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return self::$_memcache->set(
            $id,
            self::$_serialize ? serialize($data) : $data,
            $specificLifetime !== false ? $specificLifetime : self::$_lifetime
        );
    }

    /**
     * Delete cache record implementation
     *
     * @param string $id
     * @return bool
     */
    public function realRemove($id)
    {
        return @self::$_memcache->delete($id);
    }

    /**
     * Define automatic cache cleaning as available
     *
     * @return bool
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
