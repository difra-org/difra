<?php

class Cache_XCache extends Cache_Common {
	
	public $adapter = 'XCache';

	/**
	 * Constructor
	 */
	public function __construct() {

		if( !self::isAvailable() ) {
			error( 'XCache is not available!', __FILE__, __LINE__ );
		}
	}
	
	public static function isAvailable() {
		
		if( !extension_loaded( 'xcache' ) ) {
			return false;
		}
		if( !ini_get( 'xcache.var_size' ) ) {
			return false;
		}
		return true;
	}
    
	/**
	 * Test if a cache record is available for the given id and (if yes) return it (false else)
	 * @param string $id
	 * @param boolean $doNotTestCacheValidity
	 * @return string
	 */
	public function get( $id, $doNotTestCacheValidity = false ) {

		if( xcache_isset( $id ) ) {
			return xcache_get( $id );
		}
		return null;
	}
    
	/**
	 * Test if a cache record is available or not (for the given id)
	 * @param string $id cache id
	 * @return boolean
	 */
	public function test( $id ) {

		return xcache_isset( $id );
	}
    
	/**
	 * Save some string datas into a cache record
	 *
	 * @param string $id
	 * @param string $data
	 * @param int $specificLifetime
	 * @return boolean
	 */
	public function put( $id, $data, $specificLifetime = false ) {

		return xcache_set( $id, $data );
	}
    
	/**
	 * Remove a cache record
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function remove( $id ) {

		return xcache_unset( $id );
	}
    
	/**
	 * Return true if the automatic cleaning is available for the backend
	 * 
	 * @return boolean
	 */
    	public function isAutomaticCleaningAvailable() {

		return true;
	}

}
