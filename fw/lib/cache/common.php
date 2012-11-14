<?php

namespace Difra\Cache;

use Difra;

abstract class Common {

	// is class available?
	// abstracts static public function isAvailable();

	/**
	 * Получить данные из бэкэнда
	 * @param string $id
	 * @param bool   $doNotTestCacheValidity
	 *
	 * @return mixed|null
	 */
	abstract public function realGet( $id, $doNotTestCacheValidity = false );

	/**
	 * Добавить запись в бэкэнд
	 * @param string $id
	 * @param mixed  $data
	 * @param bool   $specificLifetime
	 */
	abstract public function realPut( $id, $data, $specificLifetime = false );

	/**
	 * Удаление записи из бэкэнда
	 * @param string $id
	 */
	abstract public function realRemove( $id );

	/**
	 * Проверить наличие записи в кэше
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
	 * Конструктор
	 */
	public function __construct() {

		if( !method_exists( $this, 'isAvailable' ) or !$this::isAvailable() ) {
			throw new Difra\Exception( __CLASS__ . ' requested, but that cache is not available!' );
		}
	}

	/**
	 * Получить запись из кэша
	 * @param $key
	 *
	 * @return string|null
	 */
	public function get( $key ) {

		$data = $this->realGet( Difra\Site::getInstance()->getHost() . '_' . $key );
		if( !$data or !isset( $data['expires'] ) or $data['expires'] < time() ) {
			return null;
		}
		return $data['data'];
	}

	/**
	 * Добавить запись в кэш
	 * @param string $key
	 * @param string $data
	 * @param int    $ttl
	 */
	public function put( $key, $data, $ttl = 300 ) {

		$data = array(
			'expires' => time() + $ttl,
			'data'    => $data
		);
		$this->realPut( Difra\Site::getInstance()->getHost() . '_' . $key, $data, $ttl );
	}

	/**
	 * Удалить запись из кэша
	 * @param string $key
	 */
	public function remove( $key ) {

		$this->realRemove( Difra\Site::getInstance()->getHost() . '_' . $key );
	}

	/**
	 * @deprecated
	 *
	 * @param $key
	 *
	 * @return null
	 */
	public function smartGet( $key ) {

		return $this->get( $key );
	}

	/**
	 * @deprecated
	 *
	 * @param     $key
	 * @param     $data
	 * @param int $ttl
	 */
	public function smartPut( $key, $data, $ttl = 300 ) {

		$this->put( $key, $data, $ttl );
	}

	/**
	 * @deprecated
	 *
	 * @param $key
	 */
	public function smartRemove( $key ) {

		$this->remove( $key );
	}
}
