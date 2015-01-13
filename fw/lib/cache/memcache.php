<?php

namespace Difra\Cache;

/**
 * Реализация кэширования в Memcached через модуль memcache
 * Class MemCache
 *
 * @package Difra\Cache
 */
class MemCache extends Common {

	public $adapter = \Difra\Cache::INST_MEMCACHE;

	/** @var \Memcache */
	private static $_memcache = null;
	private static $_server = false;
	private static $_port = 0;
	private static $_serialize = false;
	private static $_lifetime = 0;

	/**
	 * Возвращает true, если кэширование через данную реализацию возможно.
	 *
	 * @return bool
	 */
	public static function isAvailable() {

		if( !extension_loaded( 'memcache' ) ) {
			return false;
		}
		if( self::$_memcache ) {
			return true;
		}
		$serverList = array(
			array( 'unix:///tmp/memcache', 0 ),
			array( '127.0.0.1', 11211 ),
		);
		self::$_memcache = new \MemCache;
		foreach( $serverList as $serv ) {
			if( @self::$_memcache->pconnect( $serv[0], $serv[1] ) ) {
				self::$_server = $serv[0];
				self::$_port = $serv[1];
				return true;
			}
		}
		return false;
	}

	/**
	 * Реализация получения данных из кэша
	 *
	 * @param string $id
	 * @param bool   $doNotTestCacheValidity
	 *
	 * @return mixed|null
	 */
	public function realGet( $id, $doNotTestCacheValidity = false ) {

		$data = @self::$_memcache->get( $id );
		return self::$_serialize ? @unserialize( $data ) : $data;
	}

	/**
	 * Реализация сохранения данных в кэше
	 *
	 * @param string $id
	 * @param mixed  $data
	 * @param bool   $specificLifetime
	 *
	 * @return mixed
	 */
	public function realPut( $id, $data, $specificLifetime = false ) {

		return self::$_memcache->set( $id,
					      self::$_serialize ? serialize( $data ) : $data,
					      MEMCACHE_COMPRESSED,
					      $specificLifetime !== false ? $specificLifetime : self::$_lifetime );
	}

	/**
	 * Реализация удаления данных из кэша
	 *
	 * @param string $id
	 */
	public function realRemove( $id ) {

		@self::$_memcache->delete( $id );
	}

	/**
	 * Реализация проверки существования ключа
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function test( $key ) {

		return $this->get( $key ) ? true : false;
	}

	/**
	 * Реализация функции определения наличия автоматической подчистки кэша
	 *
	 * @return bool
	 */
	public function isAutomaticCleaningAvailable() {

		return true;
	}
}
