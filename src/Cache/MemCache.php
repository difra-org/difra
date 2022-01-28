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
    public ?string $adapter = Cache::INST_MEMCACHE;

    /**
     * Detect if backend is available
     * @return bool
     */
    public static function isAvailable(): bool
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
        self::$memcache = new \Memcache();
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
     * @return mixed
     */
    public function realGet(string $id, $doNotTestCacheValidity = false): mixed
    {
        $data = @self::$memcache->get($id);
        return self::$serialize ? @unserialize($data) : $data;
    }

    /**
     * Set cache record implementation
     * @param string $id
     * @param mixed $data
     * @param bool $specificLifetime
     * @return bool
     */
    public function realPut(string $id, mixed $data, bool $specificLifetime = false): bool
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
    public function realRemove(string $id)
    {
        @self::$memcache->delete($id);
    }

    /**
     * Test if cache record exists implementation
     * @param string $id
     * @return bool
     */
    public function test(string $id): bool
    {
        return (bool)$this->get($id);
    }

    /**
     * Define automatic cleaning is available
     * @return bool
     */
    public function isAutomaticCleaningAvailable(): bool
    {
        return true;
    }
}
