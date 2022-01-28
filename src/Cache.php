<?php

declare(strict_types=1);

namespace Difra;

/**
 * Cache factory
 * Class Cache
 * @package Difra
 */
class Cache
{
    /** Auto detect */
    public const INST_AUTO = 'Auto';
    /** Memcached module */
    public const INST_MEMCACHED = 'MemCached';
    /** Memcache module */
    public const INST_MEMCACHE = 'Memcache';
    /** Xcache */
    public const INST_XCACHE = 'XCache';
    /** Shared memory */
    public const INST_SHAREDMEM = 'Shared Memory';
    /** APCu */
    public const INST_APCU = 'APCu';
    /** Stub */
    public const INST_NONE = 'None';
    /** Default */
    public const INST_DEFAULT = self::INST_AUTO;
    /** Default TTL (seconds) */
    public const DEFAULT_TTL = 300;
    /**
     * Configured cache adapters.
     * @var array
     */
    private static array $adapters = [];

    /**
     * Builds new cache adapter or returns
     * existing one.
     * @param string $configName
     * @return \Difra\Cache\APCu|\Difra\Cache\MemCache|\Difra\Cache\MemCached|\Difra\Cache\None|\Difra\Cache\SharedMemory|\Difra\Cache\XCache
     * @throws \Difra\Exception
     */
    public static function getInstance(string $configName = self::INST_DEFAULT): Cache\APCu|Cache\MemCache|Cache\MemCached|Cache\None|Cache\SharedMemory|Cache\XCache
    {
        if ($configName == self::INST_AUTO) {
            $configName = self::detect();
        }
        return self::getAdapter($configName);
    }

    /**
     * Detect available adapter
     * @return string
     */
    private static function detect(): string
    {
        static $autoDetected = null;
        if ($autoDetected) {
            return $autoDetected;
        }
        if (!Debugger::isCachesEnabled()) {
            Debugger::addLine('Caching disabled by Debug Mode settings');
            return $autoDetected = self::INST_NONE;
        }
        if (Cache\APCu::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: APCu');
            return $autoDetected = self::INST_APCU;
        } if (Cache\XCache::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: XCache');
            return $autoDetected = self::INST_XCACHE;
        } elseif (Cache\MemCached::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: MemCached');
            return $autoDetected = self::INST_MEMCACHED;
        } elseif (Cache\MemCache::isAvailable()) {
            Debugger::addLine('Auto-detected cache type: Memcache');
            return $autoDetected = self::INST_MEMCACHE;
//        } elseif (Cache\SharedMemory::isAvailable()) {
//            Debugger::getInstance()->addLine('Auto-detected cache type: Shared Memory');
//            return $autoDetected = self::INST_SHAREDMEM;
        }
        Debugger::addLine('No cache detected');
        return $autoDetected = self::INST_NONE;
    }

    /**
     * Factory
     * @param string $configName
     * @return Cache\MemCache|Cache\MemCached|Cache\None|Cache\SharedMemory|Cache\XCache|Cache\APCu
     * @throws Exception
     */
    private static function getAdapter(string $configName): Cache\APCu|Cache\XCache|Cache\MemCached|Cache\SharedMemory|Cache\MemCache|Cache\None
    {
        if (isset(self::$adapters[$configName])) {
            return self::$adapters[$configName];
        }

        return match ($configName) {
            self::INST_APCU => self::$adapters[$configName] = new Cache\APCu(),
            self::INST_XCACHE => self::$adapters[$configName] = new Cache\XCache(),
            self::INST_SHAREDMEM => self::$adapters[$configName] = new Cache\SharedMemory(),
            self::INST_MEMCACHED => self::$adapters[$configName] = new Cache\MemCached(),
            self::INST_MEMCACHE => self::$adapters[$configName] = new Cache\MemCache(),
            self::INST_NONE => self::$adapters[$configName] = new Cache\None(),
            default => throw new Exception("Unknown cache adapter type: $configName"),
        };
    }
}
