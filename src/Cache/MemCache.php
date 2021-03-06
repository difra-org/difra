<?php

namespace Difra\Cache;

use Difra\Cache;

/**
 * Memcached (memcache extension) adapter
 * Class MemCache
 * @package Difra\Cache
 */
class MemCache extends Common
{
    /** @var \Memcache */
    private static $memcache = null;
    /** @var string Memcached server */
    private static $server = false;
    /** @var int Memcached port */
    private static $port = 0;
    /** @var bool Serialize values? */
    private static $serialize = false;
    /** @var int TTL */
    private static $lifetime = 0;
    /** @var string Adapter name */
    public $adapter = Cache::INST_MEMCACHE;

    /**
     * Detect if backend is available
     * @return bool
     */
    public static function isAvailable()
    {
        if (!extension_loaded('memcache')) {
            return false;
        }
        if (self::$memcache) {
            return true;
        }
        $serverList = [
            ['unix:///tmp/memcache', 0],
            ['127.0.0.1', 11211],
        ];
        self::$memcache = new \MemCache;
        foreach ($serverList as $serv) {
            if (@self::$memcache->pconnect($serv[0], $serv[1])) {
                self::$server = $serv[0];
                self::$port = $serv[1];
                return true;
            }
        }
        return false;
    }

    /**
     * Get cache record implementation
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed|null
     */
    public function realGet($id, $doNotTestCacheValidity = false)
    {
        $data = @self::$memcache->get($id);
        return self::$serialize ? @unserialize($data) : $data;
    }

    /**
     * Set cache record implementation
     * @param string $id
     * @param mixed $data
     * @param bool $specificLifetime
     * @return mixed
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return self::$memcache->set(
            $id,
            self::$serialize ? serialize($data) : $data,
            MEMCACHE_COMPRESSED,
            $specificLifetime !== false ? $specificLifetime : self::$lifetime
        );
    }

    /**
     * Delete cache record implementation
     * @param string $id
     */
    public function realRemove($id)
    {
        @self::$memcache->delete($id);
    }

    /**
     * Test if cache record exists implementation
     * @param string $key
     * @return bool
     */
    public function test($key)
    {
        return $this->get($key) ? true : false;
    }

    /**
     * Define automatic cleaning is available
     * @return bool
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
