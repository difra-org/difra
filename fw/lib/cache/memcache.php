<?php

namespace Difra\Cache;

use Difra\Cache;

/**
 * Memcached (memcache extension) adapter
 * Class MemCache
 *
 * @package Difra\Cache
 */
class MemCache extends Common
{
    /** @var \Memcache */
    private static $_memcache = null;
    private static $_server = false;
    private static $_port = 0;
    private static $_serialize = false;
    private static $_lifetime = 0;
    public $adapter = Cache::INST_MEMCACHE;

    /**
     * Detect if backend is available
     *
     * @return bool
     */
    public static function isAvailable()
    {
        if (!extension_loaded('memcache')) {
            return false;
        }
        if (self::$_memcache) {
            return true;
        }
        $serverList = [
            ['unix:///tmp/memcache', 0],
            ['127.0.0.1', 11211],
        ];
        self::$_memcache = new \MemCache;
        foreach ($serverList as $serv) {
            if (@self::$_memcache->pconnect($serv[0], $serv[1])) {
                self::$_server = $serv[0];
                self::$_port = $serv[1];
                return true;
            }
        }
        return false;
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
     * Set cache record implementation
     *
     * @param string $id
     * @param mixed  $data
     * @param bool   $specificLifetime
     * @return mixed
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return self::$_memcache->set(
            $id,
            self::$_serialize ? serialize($data) : $data,
            MEMCACHE_COMPRESSED,
            $specificLifetime !== false ? $specificLifetime : self::$_lifetime
        );
    }

    /**
     * Delete cache record implementation
     *
     * @param string $id
     */
    public function realRemove($id)
    {
        @self::$_memcache->delete($id);
    }

    /**
     * Test if cache record exists implementation
     *
     * @param string $key
     * @return bool
     */
    public function test($key)
    {
        return $this->get($key) ? true : false;
    }

    /**
     * Define automatic cleaning is available
     *
     * @return bool
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
