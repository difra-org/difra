<?php

namespace Difra;

/**
 * Автоматическая подгрузка классов
 * Class Autoloader
 * @package Difra
 */
class Autoloader {

	/** @var array Чёрный список классов */
	static $bl = array( 'sqlite3' );

	/**
	 * Синглтон
	 * @return self
	 */
	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Загрузчик классов
	 * @param $class
	 */
	public static function load( $class ) {

		if( in_array( strtolower( trim( $class, '\\' ) ), self::$bl ) ) {
			return;
		}
		$class = ltrim( $class, '\\' );
		$parts = explode( '\\', $class );
		if( $parts[0] != 'Difra' ) {
			$path = DIR_ROOT . 'lib/';
		} elseif( $parts[0] == 'Difra' and $parts[1] == 'Plugins' ) {
			$name = strtolower( $parts[2] );
			// классы вида Plugins/Name ищем в plugins/name/lib/name.php
			if( sizeof( $parts ) == 3 ) {
				$parts = array();
				$path = DIR_PLUGINS . "$name/lib/$name";
			} else {
				$parts = array_slice( $parts, 3 );
				$path = DIR_PLUGINS . "$name/lib/";
			}
		} else {
			$path = defined( 'DIR_FW' ) ? DIR_FW . 'lib/' : __DIR__ . '/';
			array_shift( $parts );
		}
		$filename = $path . strtolower( implode( '/', $parts ) ) . '.php';
		//if( !class_exists( '\\Difra\\Debugger' ) or Debugger::getInstance()->isEnabled() or file_exists( $filename ) ) {
		@include_once( $filename );
		//}
	}

	/**
	 * Обработчик событий
	 * @throws exception
	 */
	public static function register() {

		if( function_exists( 'spl_autoload_register' ) ) {
			if( !spl_autoload_register( 'Difra\Autoloader::load' ) ) {
				throw new exception( 'Can\'t register Autoloader' );
			}
		}
	}
}

// Регистрация обработчика событий
Autoloader::register();
