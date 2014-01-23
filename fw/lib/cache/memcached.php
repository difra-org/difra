<?php

namespace Difra\Cache;

/**
 * Реализация кэширования через расширение memcached
 * Class MemCached
 *
 * @package Difra\Cache
 */
class MemCached extends Common {

	public $adapter = \Difra\Cache::INST_MEMCACHED;

	/** @var \Memcached */
	private static $_memcache = null;
	private static $_serialize = true;
	private static $_lifetime = 0;

	/**
	 * Определение работоспособности расширения
	 * @return bool
	 */
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
				return false;
			}
			return true;
		} catch( \Difra\Exception $ex ) {
			return false;
		}
	}

	/**
	 * Реализация получения данных из кэша
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
	 * Реализация определения существования ключа
	 * @param string $id
	 *
	 * @return bool
	 */
	public function test( $id ) {

		$data = $this->get( $id );
		return !empty( $data );
	}

	/**
	 * Реализация сохранения данных в кэше
	 * @param string $id
	 * @param mixed  $data
	 * @param bool   $specificLifetime
	 *
	 * @return bool
	 */
	public function realPut( $id, $data, $specificLifetime = false ) {

		return self::$_memcache->set( $id,
					      self::$_serialize ? serialize( $data ) : $data,
					      $specificLifetime !== false ? $specificLifetime : self::$_lifetime );
	}

	/**
	 * Реализация удаления данных из кэша
	 * @param string $id
	 *
	 * @return bool
	 */
	public function realRemove( $id ) {

		return @self::$_memcache->delete( $id );
	}

	/**
	 * Проверка наличия автоматической подчистки кэша
	 * @return bool
	 */
	public function isAutomaticCleaningAvailable() {

		return true;
	}
}
