<?php

namespace Difra;

/**
 * Cache factory
 * Class Cache
 * @package Difra
 */
class Cache
{
    const INST_AUTO = 'Auto detect';
    const INST_MEMCACHED = 'MemCached';
    const INST_MEMCACHE = 'Memcache';
    const INST_XCACHE = 'XCache';
    const INST_SHAREDMEM = 'Shared Memory';
    const INST_NONE = 'None';
    const INST_DEFAULT = self::INST_AUTO;
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

    private static function detect()
    {
        static $autoDetected = null;
        if ($autoDetected) {
            return $autoDetected;
        }
        if (!Debugger::isCachesEnabled()) {
            Debugger::addLine('Caching disabled by Debug Mode settings');
            return $autoDetected;
        }
        if (Cache\XCache::isAvailable()) {
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

    private static function getAdapter($configName)
    {
        if (isset(self::$adapters[$configName])) {
            return self::$adapters[$configName];
        }

        switch ($configName) {
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
                // todo: wrong adapter name: warning?
                return self::getAdapter(self::INST_NONE);
        }
    }
}
