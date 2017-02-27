<?php

namespace Difra;

/**
 * Cache factory
 * Class Cache
 * @package Difra
 */
class Cache
{
    /** Auto detect */
    const INST_AUTO = 'Auto detect';
    /** Memcached module */
    const INST_MEMCACHED = 'MemCached';
    /** Memcache module */
    const INST_MEMCACHE = 'Memcache';
    /** Xcache */
    const INST_XCACHE = 'XCache';
    /** Shared memory */
    const INST_SHAREDMEM = 'Shared Memory';
    /** APCu */
    const INST_APCU = 'APCu';
    /** Stub */
    const INST_NONE = 'None';
    /** Default */
    const INST_DEFAULT = self::INST_AUTO;
    /** Default TTL (seconds) */
    const DEFAULT_TTL = 300;
    /**
     * Configured cache adapters.
     * @var array
     */
    private static $adapters = [];

    /**
     * Builds new cache adapter or returns
     * existing one.
     * @param string $configName
     * @return \Difra\Cache\Common
     */
    public static function getInstance($configName = self::INST_DEFAULT)
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
    private static function detect()
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
    private static function getAdapter($configName)
    {
        if (isset(self::$adapters[$configName])) {
            return self::$adapters[$configName];
        }

        switch ($configName) {
            case self::INST_APCU:
                return self::$adapters[$configName] = new Cache\APCu();
            case self::INST_XCACHE:
                return self::$adapters[$configName] = new Cache\XCache();
            case self::INST_SHAREDMEM:
                return self::$adapters[$configName] = new Cache\SharedMemory();
            case self::INST_MEMCACHED:
                return self::$adapters[$configName] = new Cache\MemCached();
            case self::INST_MEMCACHE:
                return self::$adapters[$configName] = new Cache\MemCache();
            case self::INST_NONE:
                return self::$adapters[$configName] = new Cache\None();
            default:
                throw new Exception("Unknown cache adapter type: $configName");
        }
    }
}
