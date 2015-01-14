<?php

namespace Difra\Unify;

/**
 * Class Storage
 *
 * @package Difra\Unify
 */
abstract class Storage {

	/** @var Object[string $objKey][id] */
	static public $objects = [];
	/** @var array Список доступных объектов в формате название => имя_класса */
	static protected $classes = [];

	/**
	 * @param string[] $list Объекты для добавления в список
	 */
	final static public function registerObjects($list) {

		if(!$list or empty($list)) {
			return;
		}
		if(!is_array($list)) {
			$list = [$list];
		}
		/** @var $class Item */
		foreach($list as $class) {
			self::$classes[$class::getObjKey()] = $class;
		}
	}

	/**
	 * Получение объекта по $objKey
	 *
	 * @param string $objKey  Имя объекта
	 * @param mixed  $primary Значение primary-поля (например, id)
	 * @return static
	 * @throws \Difra\Exception
	 */
	final public static function getObj($objKey, $primary) {

		$class = self::getClass($objKey);
		if(!$class) {
			throw new \Difra\Exception("Can't find class for object '{$objKey}''");
		}
		return $class::get($primary);
	}

	/**
	 * Получение имени класса по objKey
	 *
	 * @param $objKey
	 * @return string|Item|null
	 */
	final static public function getClass($objKey) {

		return isset(self::$classes[$objKey]) ? '\\' . self::$classes[$objKey] : null;
	}

	/**
	 * Create new item object by $objKey

	 *
*@param string $objKey
	 * @return static
	 * @throws \Difra\Exception
	 */
	final public static function createObj($objKey) {

		$class = self::getClass($objKey);
		if(!$class) {
			throw new \Difra\Exception("Can't find class for object '{$objKey}''");
		}
		return $class::create();
	}

	final public static function getAllClasses() {

		return self::$classes;
	}
}