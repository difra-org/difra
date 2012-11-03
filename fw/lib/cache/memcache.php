<?php

namespace Difra\Cache;

class MemCache extends Common {
	
	public $adapter = 'MemCache';

	private static $_memcache  = null;
	private static $_server    = false;
	private static $_port      = 0;
	private static $_serialize = false;
	private static $_lifetime  = 0;
    
	public function __construct() {

//		if( !self::isAvailable() ) {
//			throw new exception( 'Memcache is not available', __FILE__, __LINE__ );
//		}
	}
	
	public static function isAvailable() {

		if( !extension_loaded( 'memcache' ) ) {
			return false;
		}
		if( self::$_memcache ) {
			return true;
		}
		$serverList = array(
			array( 'unix:///tmp/memcache', 0 ), array( '127.0.0.1', 11211 ),
		);
		self::$_memcache = new \MemCache;
		foreach( $serverList as $serv ) {
			if( @self::$_memcache->pconnect( $serv[0], $serv[1] ) ) {
				self::$_server	= $serv[0];
				self::$_port	= $serv[1];
				return true;
			}
		}
		return false;
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
		
		$data = self::load( $id );
		return !empty( $data );
	}
    
	public function realPut( $id, $data, $specificLifetime = false ) {
		
		return self::$_memcache->set( $id, self::$_serialize ? serialize( $data ) : $data, MEMCACHE_COMPRESSED, $specificLifetime !== false ? $specificLifetime : self::$_lifetime );
	}
	
	public function realRemove( $id ) {

		return @self::$_memcache->delete( $id );
	}

	public function isAutomaticCleaningAvailable() {

		return false;
	}

}
