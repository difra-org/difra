<?php

namespace Difra;

/**
 * Class Unify
 *
 * @package Difra\Unify
 */
final class Unify {

	//
	// работа с классами и объектами
	//

	/** @var Object[string $objKey][id] */
	static public $objects = array();

	/** @var string[string $objKey], например 'blog' => 'Difra\Plugins\Blogs\Blog' */
	static private $classes = array();

	/**
	 * Регистрация класса
	 * @param $name
	 * @param $path
	 */
	static public function registerClass( $name, $path ) {

		self::$classes[$name] = $path;
	}

	/**
	 * Получение пути к классу
	 * @param $name
	 * @return null
	 */
	static public function getPath( $name ) {

		return isset( self::$classes[$name] ) ? self::$classes[$name] : null;
	}
}