<?php

namespace Difra\Libs;

include_once( __DIR__ . '/esapi/trunk/src/ESAPI.php' );

/**
 * Class ESAPI
 *
 * @package Difra\Libs
 * @deprecated
 */
class ESAPI {

	/**
	 * Фабричные функции
	 *
	 */

	/**
	 * @return \ESAPI
	 */
	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new \ESAPI( __DIR__ . '/esapi/ESAPI.xml' );
	}

	/**
	 * @return \Validator
	 */
	public static function validator() {

		return self::getInstance()->getValidator();
	}

	/**
	 * @return \Encoder
	 */
	public static function encoder() {

		return self::getInstance()->getEncoder();
	}

	/**
	 * Валидация ввода
	 *
	 */

	/**
	 * Проверка валидности URL
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public static function validateURL( $url ) {

		try {
			return @self::getInstance()->getValidator()->isValidInput( "URLContext", $url, "URL", 255, false );
		} catch( \Exception $ex ) {
			return false;
		}
	}
}