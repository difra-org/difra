<?php

namespace Difra\Cache;
use Difra;

class XCache extends Common {
	
	public $adapter = 'XCache';

	/**
	 * Constructor
	 */
	public function __construct() {

		if( !self::isAvailable() ) {
			throw new Difra\Exception( 'XCache is not available!' );
		}
	}
	
	public static function isAvailable() {
		
		try {
			if( !extension_loaded( 'xcache' ) or !ini_get( 'xcache.var_size' ) ) {
				return false;
			}
			@xcache_isset( 'test' );
			if( $e = error_get_last() and $e['file'] == __FILE__ ) {
				return false;
			}
		} catch( Difra\Exception $ex ) {
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
	public function realGet( $id, $doNotTestCacheValidity = false ) {

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
	public function realPut( $id, $data, $specificLifetime = false ) {

		return xcache_set( $id, $data, $specificLifetime );
	}
    
	/**
	 * Remove a cache record
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function realRemove( $id ) {

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
