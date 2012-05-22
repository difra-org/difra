<?php

namespace Difra\Cache;
use Difra;

abstract class Common {

	// is class available?
//	abstracts static public function isAvailable();
	// return cache record or null if record is not found
	abstract public function realGet( $id, $doNotTestCacheValidity = false );
	// test if cache record exists
	abstract public function test( $id );
	// create or update cache record
	abstract public function realPut( $id, $data, $specificLifetime = false );
	// delete cache record
	abstract public function realRemove( $id );
	// is the automatic cleaning available for the backend?
	abstract public function isAutomaticCleaningAvailable();

	// constructor
	public function __construct() {
		
		if( !method_exists( $this, 'isAvailable') or !$this::isAvailable() ) {
			throw new Difra\Exception( __CLASS__ . ' requested, but that cache is not available!' );
		}
	}

	public function get( $key ) {

		$data = $this->realGet( Difra\Site::getInstance()->getHost() . '_' . $key );
		if( !$data ) {
			return null;
		}
		if( $data['expires'] < time() ) {
			return null;
		}
		return $data['data'];
	}
	
	public function put( $key, $data, $ttl = 300 ) {
		
		$data = array(
			'expires' => time() + $ttl,
			'data' => $data
		);
		$this->realPut( Difra\Site::getInstance()->getHost() . '_' . $key, $data, $ttl );
	}
	
	public function remove( $key ) {
		
		$this->realRemove( Difra\Site::getInstance()->getHost() . '_' . $key );
	}

	public function smartGet( $key ) {
		return $this->get( $key );
	}

	public function smartPut( $key, $data, $ttl = 300 ) {
		$this->put( $key, $data, $ttl );
	}

	public function smartRemove( $key ) {
		$this->remove( $key );
	}
}
