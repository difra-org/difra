<?php

namespace Difra\Cache;

/**
 * Abstract cache adapter class
 * Class Common

 *
*@package Difra\Cache
 */
abstract class Common {

	//abstract static public function isAvailable();

	const SESS_PREFIX = 'session:';
	/** @var string */
	public $adapter = null;

	/**
	 * Конструктор
	 */
	public function __construct() {

		if(!method_exists($this, 'isAvailable') or !$this::isAvailable()) {
			throw new \Difra\Exception(__CLASS__ . ' requested, but that cache is not available!');
		}
	}

	/**
	 * Проверить наличие записи в кэше
	 *
	 * @deprecated
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	abstract public function test( $id );

	/**
	 * Возвращает true, если бэкэнд поддерживает автоматическое удаление старых данных
	 *
	 * @return bool
	 */
	abstract public function isAutomaticCleaningAvailable();

	/**
	 * @deprecated
	 * @param $key
	 * @return null
	 */
	public function smartGet($key) {

		return $this->get($key);
	}

	/**
	 * Получить запись из кэша
	 *
	 * @param $key
	 *
	 * @return string|null
	 */
	public function get( $key ) {

		$data = $this->realGet(\Difra\Envi::getSubsite() . '_' . $key);
		if( !$data or !isset( $data['expires'] ) or $data['expires'] < time() ) {
			return null;
		}
		return $data['data'];
	}

	/**
	 * Получить данные из бэкэнда
	 *
	 * @param string $id
	 * @param bool   $doNotTestCacheValidity
	 *
	 * @return mixed|null
	 */
	abstract public function realGet($id, $doNotTestCacheValidity = false);

	/**
	 * @deprecated
	 * @param     $key
	 * @param     $data
	 * @param int $ttl
	 */
	public function smartPut($key, $data, $ttl = 300) {

		$this->put($key, $data, $ttl);
	}

	/**
	 * Добавить запись в кэш
	 *
	 * @param string $key
	 * @param string $data
	 * @param int    $ttl
	 */
	public function put( $key, $data, $ttl = 300 ) {

		$data = [
			'expires' => time() + $ttl,
			'data' => $data
		];
		$this->realPut(\Difra\Envi::getSubsite() . '_' . $key, $data, $ttl);
	}

	/**
	 * Добавить запись в бэкэнд
	 *
	 * @param string $id
	 * @param mixed  $data
	 * @param bool   $specificLifetime
	 */
	abstract public function realPut($id, $data, $specificLifetime = false);

	/**
	 * @deprecated
	 *
	 * @param $key
	 */
	public function smartRemove($key) {

		$this->remove($key);
	}

	/**
	 * Удалить запись из кэша
	 *
	 * @param string $key
	 */
	public function remove($key) {

		$this->realRemove(\Difra\Envi::getSubsite() . '_' . $key);
	}

	/**
	 * Удаление записи из бэкэнда
	 *
	 * @param string $id
	 */
	abstract public function realRemove($id );

	/**
	 * Set session handler in current cache, if available
	 */
	public function setSessionsInCache() {

		static $set = false;
		if( $set ) {
			return;
		}
		if( \Difra\Cache::getInstance()->adapter == \Difra\Cache::INST_NONE ) {
			return;
		}

		session_set_save_handler(
		// open
			function ( $s, $n ) {

				return true;
			},
			// close
			function () {

				return true;
			},
			// read
			function ( $id ) {

				return \Difra\Cache::getInstance()->get( self::SESS_PREFIX . $id ) ? : '';
			},
			// write
			function ( $id, $data ) {

				if( !$data ) {
					return false;
				}
				\Difra\Cache::getInstance()->put( self::SESS_PREFIX . $id, $data, 86400 ); // 24h
				return true;
			},
			// destroy
			function ( $id ) {

				\Difra\Cache::getInstance()->remove( self::SESS_PREFIX . $id );
			},
			// garbage collector
			function ( $expire ) {

				return true;
			}
		);
		$set = true;
	}
}
