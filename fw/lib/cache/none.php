<?php

namespace Difra\Cache;

/**
 * Реализация отсутствия кэша
 * Class None
 *
 * @package Difra\Cache
 */
class None extends Common {

	public $adapter = \Difra\Cache::INST_NONE;

	/**
	 * Constructor
	 */
	public function __construct() {

		if( !self::isAvailable() ) {
			throw new \Difra\Exception( 'Cache_None is not available!' );
		}
	}

	/**
	 * Определение наличия
	 * @return bool
	 */
	public static function isAvailable() {

		return true;
	}

	/**
	 * Получение данных
	 * @param string  $id
	 * @param boolean $doNotTestCacheValidity
	 *
	 * @return string
	 */
	public function realGet( $id, $doNotTestCacheValidity = false ) {

		return null;
	}

	/**
	 * Проверка существования ключа
	 * @param string $id cache id
	 *
	 * @return boolean
	 */
	public function test( $id ) {

		return false;
	}

	/**
	 * Сохранение данных
	 * @param string   $id
	 * @param string   $data
	 * @param bool|int $specificLifetime
	 *
	 * @return boolean
	 */
	public function realPut( $id, $data, $specificLifetime = false ) {

		return false;
	}

	/**
	 * Удаление данных
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function realRemove( $id ) {

		return false;
	}

	/**
	 * Наличие автоматической подчистки кэша
	 * @return boolean
	 */
	public function isAutomaticCleaningAvailable() {

		return true;
	}
}
