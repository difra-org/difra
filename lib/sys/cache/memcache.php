<?php

class Cache_MemCache {
	
	public $adapter = 'MemCache';

	private static $_memcache  = null;
	private static $_server    = false;
	private static $_port      = 0;
	private static $_serialize = false;
	private static $_lifetime  = 0;
    
	public function __construct() {

		if( !self::isAvailable() ) {
			error( 'Memcache is not available', __FILE__, __LINE__ );
		}
		/* // Должен быть уже подключен
		if( !self::$_memcache ) {
			self::$_memcache = new Memcache;
		}
		self::$_memcache->addServer( $this->_server, $this->_port, 1 );
		 */
	}
	
	public static function isAvailable() {
	
		if( !extension_loaded( 'memcache' ) ) {
			return false;
		}
		$serverList = array(
				    array( 'unix:///tmp/memcache', 0 ),
				    array( '127.0.0.1', 11211 ),
				    );
		if( !self::$_memcache ) {
			self::$_memcache = new Memcache;
		} else {
			return true;
		}
		foreach( $serverList as $serv ) {
			if( @self::$_memcache->connect( $serv[0], $serv[1] ) ) {
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
    
	/**
	 * Test if a cache record is available for the given id and (if yes) return it (false else)
	 * @param string $id
	 * @param boolean $doNotTestCacheValidity
	 * @return string
	 */
	public function get( $id, $doNotTestCacheValidity = false ) {
		
		$data = @self::$_memcache->get( $id );
		return self::$_serialize ? @unserialize( $data ) : $data;
	}
    
	/**
	 * Test if a cache record is available or not (for the given id)
	 * @param string $id
	 * @return boolean
	 */
	public function test( $id ) {
		
		$data = self::load( $id );
		return !empty( $data );
	}
    
	/**
	 * Save some string datas into a cache record
	 * @param string $id
	 * @param string $data
	 * @param int $specificLifetime
	 * @return boolean true if no problem
	 */
	public function put( $id, $data, $specificLifetime = false ) {
		
		return self::$_memcache->set( $id, self::$_serialize ? serialize( $data ) : $data, MEMCACHE_COMPRESSED, $specificLifetime !== false ? $specificLifetime : self::$_lifetime );
	}
	
	/**
	 * Remove cache record
	 * @param string $id
	 * @return boolean true if no problem
	 */
	public function remove( $id ) {

		return self::$_memcache->delete( $id );
	}

	/**
	 * Returns true if the automatic cleaning is available for the backend
	 *
	 * @return boolean
	 */
	public function isAutomaticCleaningAvailable() {

		return false;
	}

}
