<?php

class Cache_XCache {

	/**
	 * Constructor
	 */
	public function __construct() {

		if( !extension_loaded( 'xcache' ) ) {
			error( 'XCache is not loaded!', __FILE__, __LINE__ );
		}
	}
    
	/**
	 * Test if a cache record is available for the given id and (if yes) return it (false else)
	 * @param string $id
	 * @param boolean $doNotTestCacheValidity
	 * @return string
	 */
	public function load( $id, $doNotTestCacheValidity = false ) {

		if( xcache_isset( $id ) ) {
			return xcache_get( $id );
		}
		return false;
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
	public function save( $id, $data, $specificLifetime = false ) {

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
