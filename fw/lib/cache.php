<?php

namespace Difra;

/**
 * Cache factory
 * Class Cache
 *
 * @package Difra
 */
class Cache
{
	const INST_AUTO = 'auto';
	const INST_MEMCACHED = 'memcached';
	const INST_MEMCACHE = 'memcache';
	const INST_XCACHE = 'xcache';
	const INST_SHAREDMEM = 'shm';
	const INST_NONE = 'none';
	const INST_DEFAULT = self::INST_AUTO;
	/**
	 * Configured cache adapters.
	 *
	 * @var array
	 */
	private static $_adapters = [];

	/**
	 * Builds new cache adapter or returns
	 * existing one.
	 *
	 * @param string $configName
	 * @return \Difra\Cache\Common
	 */
	public static function getInstance($configName = self::INST_DEFAULT)
	{
		// adapter type auto detection
		if ($configName == self::INST_AUTO) {
			static $_auto = null;
			if ($_auto) {
				return self::getInstance($_auto);
			}
			if (!Debugger::isCachesEnabled()) {
				Debugger::addLine("Caching disabled by Debug Mode settings");
				return self::getInstance($_auto = self::INST_NONE);
			}
			if (Cache\XCache::isAvailable()) {
				Debugger::addLine("Auto-detected cache type: XCache");
				return self::getInstance($_auto = self::INST_XCACHE);
			} elseif (Cache\MemCached::isAvailable()) {
				Debugger::addLine("Auto-detected cache type: MemCached");
				return self::getInstance($_auto = self::INST_MEMCACHED);
			} elseif (Cache\MemCache::isAvailable()) {
				Debugger::addLine("Auto-detected cache type: Memcache");
				return self::getInstance($_auto = self::INST_MEMCACHE);
//			} elseif( Cache\SharedMemory::isAvailable() ) {
//				Debugger::getInstance()->addLine( "Auto-detected cache type: Shared Memory" );
//				return self::getInstance( $_auto = self::INST_SHAREDMEM );
			} else {
				Debugger::addLine("No cache detected");
				return self::getInstance($_auto = self::INST_NONE);
			}
		}

		// return adapter if exists
		if (isset(self::$_adapters[$configName])) {
			return self::$_adapters[$configName];
		}

		// create new adapter
		switch ($configName) {
			case self::INST_XCACHE:
				self::$_adapters[$configName] = new Cache\XCache();
				return self::$_adapters[$configName];
			case self::INST_SHAREDMEM:
				self::$_adapters[$configName] = new Cache\SharedMemory();
				return self::$_adapters[$configName];
			case self::INST_MEMCACHED:
				self::$_adapters[$configName] = new Cache\MemCached();
				return self::$_adapters[$configName];
			case self::INST_MEMCACHE:
				self::$_adapters[$configName] = new Cache\MemCache();
				return self::$_adapters[$configName];
			default:
				if (!isset(self::$_adapters[self::INST_NONE])) {
					self::$_adapters[self::INST_NONE] = new Cache\None();
				}
				return self::$_adapters[self::INST_NONE];
		}
	}
}

