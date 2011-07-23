<?php

namespace Difra\Cache;

class MemCached extends Common {
	
	public $adapter = 'MemCached';

	private static $_memcache  = null;
	private static $_serialize = true;
	private static $_lifetime  = 0;
    
	public function __construct() {

//		if( !self::isAvailable() ) {
//			throw new exception( 'Memcache is not available', __FILE__, __LINE__ );
//		}
	}
	
	public static function isAvailable() {

		try {
			if( !extension_loaded( 'memcached' ) ) {
				return false;
			}
			if( self::$_memcache ) {
				return true;
			}
			
			self::$_memcache = new \MemCached;
			$currentServers = self::$_memcache->getServerList();
			if( empty( $currentServers ) ) {
				self::$_memcache->addServers( array( array( '127.0.0.1', '11211', '10' ) ) );
			}
			return (bool) self::$_memcache->getVersion();
		} catch( \Difra\Exception $ex ) {
			return false;
		}
	}

	public function getInstance() {

		static $_instance;
		return $_instance ? $_instance : $_instance = new self;
	}
    
	public function realGet( $id, $doNotTestCacheValidity = false ) {
		
		$data = @self::$_memcache->get( $id );
		return self::$_serialize ? @unserialize( $data ) : $data;
	}
    
	public function test( $id ) {
		
		$data = self::realGet( $id );
		return !empty( $data );
	}
    
	public function realPut( $id, $data, $specificLifetime = false ) {
		
		return self::$_memcache->set( $id, self::$_serialize ? serialize( $data ) : $data, $specificLifetime !== false ? $specificLifetime : self::$_lifetime );
	}
	
	public function realRemove( $id ) {

		return @self::$_memcache->delete( $id );
	}

	public function isAutomaticCleaningAvailable() {

		return false;
	}

}
