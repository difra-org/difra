<?php

namespace Difra\Unify;

use Difra\Exception;

/**
 * Class Storage
 *
 * @package Difra\Unify
 */
class Storage {

	/** @var string[string $name] Список доступных объектов в формате название => имя_класса */
	static protected $classes = array();

	/** @var Object[string $objKey][id] */
	static public $objects = array();

	/**
	 * @param string[string] $list Объекты для добавления в список
	 */
	final static public function registerObjects( $list ) {

		if( !$list ) {
			return;
		}
		foreach( $list as $objKey => $class ) {
			self::$classes[$objKey] = $class;
		}
	}

	/**
	 * Получение имени класса по objKey
	 *
	 * @param $objKey
	 * @return string|null
	 */
	final static public function getClass( $objKey ) {

		return isset( self::$classes[$objKey] ) ? self::$classes[$objKey] : null;
	}

	/**
	 * Получение объекта по $objKey
	 * @param $objKey
	 * @param $primary
	 * @throws Exception
	 */
	final public static function getObj( $objKey, $primary ) {

		$class = self::getClass( $objKey );
		if( !$class ) {
			throw new Exception( "Can't find class for object '{$objKey}''" );
		}
		return $class::get( $primary );
	}
}