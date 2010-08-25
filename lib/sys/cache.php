<?php

class Cache
{
    
	const INST_MEMCACHED    = 'memcached';
	const INST_FILE         = 'default';
	const INST_XCACHE       = 'xcache';
	const INST_SHAREDMEM    = 'shm';
	const INST_DEFAULT      = self::INST_MEMCACHED;

	/**
	 * Configured cache adapters.
	 *
	 * @var array
	 */
	private static $_adapters = array();

	/**
	 * Builds new cache adapter or returns
	 * existing one.
	 *
	 * @param string $configName
	 * @return Zend_Cache_Core
	 */
	public static function getInstance( $configName = self::INST_DEFAULT )
	{
		// Don't create adapters twice.
		if( isset( self::$_adapters[$configName] ) ) {
			return self::$_adapters[$configName];
		}
    
		if( $configName == self::INST_XCACHE ) {
			self::$_adapters[$configName] = new Cache_XCache();
    			return self::$_adapters[$configName];
		} elseif( $configName == self::INST_SHAREDMEM ) {
			self::$_adapters[$configName] = new Cache_SharedMemory();
			return self::$_adapters[$configName];
		} else {
			self::$_adapters[$configName] = new Cache_MemCache();
			return self::$_adapters[$configName];
		}
	}
}

