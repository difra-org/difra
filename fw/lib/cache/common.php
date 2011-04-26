<?php

abstract class Cache_Common {

	// is class available?
//	abstract static public function isAvailable();
	// return cache record or null if record is not found
	abstract public function get( $id, $doNotTestCacheValidity = false );
	// test if cache record exists
	abstract public function test( $id );
	// create or update cache record
	abstract public function put( $id, $data, $specificLifetime = false );
	// delete cache record
	abstract public function remove( $id );
	// is the automatic cleaning available for the backend?
	abstract public function isAutomaticCleaningAvailable();

	// constructor
	public function __construct() {
		
		if( !method_exists( self, 'isAvailable') or !self::isAvailable() ) {
			throw new exception( __CLASS__ . ' requested, but that cache is not available!' );
		}
	}

	public function smartGet( $key ) {
		
		$data = $this->get( $key );
		if( $this->isAutomaticCleaningAvailable() or !$data ) {
			return $data;
		}
		if( $data['system'] != Site::getInstance()->bigVersion or $data['expires'] < time() ) {
			return null;
		}
		return $data['data'];
	}
	
	public function smartPut( $key, $data, $ttl = 60 ) {
		
		if( !$this->isAutomaticCleaningAvailable() ) {
			$data = array(
				'system' => Site::getInstance()->bigVersion,
				'expires' => time() + $ttl,
				'data' => $data
			);
		}
		$this->put( $key, $data, $ttl );
	}
}