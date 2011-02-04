<?php

class Cache_None {
	
	public $adapter = 'None';

	/**
	 * Constructor
	 */
	public function __construct() {

		if( !self::isAvailable() ) {
			error( 'Cache_None is not available!', __FILE__, __LINE__ );
		}
	}
	
	public static function isAvailable() {
		
		return true;
	}
    
	/**
	 * Test if a cache record is available for the given id and (if yes) return it (false else)
	 * @param string $id
	 * @param boolean $doNotTestCacheValidity
	 * @return string
	 */
	public function load( $id, $doNotTestCacheValidity = false ) {

		return false;
	}
    
	/**
	 * Test if a cache record is available or not (for the given id)
	 * @param string $id cache id
	 * @return boolean
	 */
	public function test( $id ) {

		return false;
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

		return false;
	}
    
	/**
	 * Remove a cache record
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function remove( $id ) {

		return false;
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
