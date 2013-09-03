<?php

namespace Difra\Unify;

use Difra\Exception;

/**
 * Class Storage
 *
 * @package Difra\Unify
 */
abstract class Storage {

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
	 *
	 * @return string|null
	 */
	final static public function getClass( $objKey ) {

		return isset( self::$classes[$objKey] ) ? '\\' . self::$classes[$objKey] : null;
	}

	/**
	 * Получение объекта по $objKey
	 * @param $objKey
	 * @param $primary
	 *
	 * @return static
	 * @throws Exception
	 */
	final public static function getObj( $objKey, $primary ) {

		$class = self::getClass( $objKey );
		if( !$class ) {
			throw new Exception( "Can't find class for object '{$objKey}''" );
		}
		/** @var $class Item */
		return $class::get( $primary );
	}

	/**
	 * Получение статуса таблиц в базе
	 * @param \DOMElement|\DOMNode $node
	 */
	final public static function getDbStatusXML( $node ) {

		if( empty( self::$classes ) ) {
			$node->setAttribute( 'empty', 1 );
			return;
		}
		foreach( self::$classes as $objKey => $className ) {
			$objNode = $node->appendChild( $node->ownerDocument->createElement( $objKey ) );
			/** @var \Difra\Unify\Item $className */
			$className::getObjDbStatusXML( $objNode );
		}
	}
}