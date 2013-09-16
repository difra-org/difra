<?php

namespace Difra;

include_once( __DIR__ . '/debugger.php' );

/**
 * Автоматическая подгрузка классов
 * Class Autoloader
 *
 * @package Difra
 */
class Autoloader {

	/** @var array Чёрный список классов */
	private static $bl = array( 'sqlite3' );

	/**
	 * Загрузчик классов
	 * @param $class
	 *
	 * @throws Exception
	 */
	public static function load( $class ) {

		if( in_array( strtolower( trim( $class, '\\' ) ), self::$bl ) ) {
			return;
		}
		$file = self::class2file( $class );
//		if( Debugger::isConsoleEnabled() == Debugger::CONSOLE_ENABLED ) {
//			if( !file_exists( $file ) ) {
////				echo 'File "' . $file . "' for class '" . $class . '" not found.';
//				throw new Exception( 'File "' . $file . "' for class '" . $class . '" not found.' );
//			}
//		}
		/** @noinspection PhpIncludeInspection */
		@include_once( $file );
	}

	/**
	 * Возвращает имя файла для заданного класса
	 * @param string $class
	 *
	 * @return string
	 */
	public static function class2file( $class ) {

		$class = ltrim( $class, '\\' );
		$parts = explode( '\\', $class );
		if( $parts[0] != 'Difra' ) {
			$path = DIR_ROOT . 'lib/';
		} elseif( sizeof( $parts ) > 4 and $parts[0] == 'Difra' and $parts[1] == 'Plugins' and $parts[3] == 'Objects' ) {
			$plugin = strtolower( $parts[2] );
			$parts = array_slice( $parts, 4 );
			$path = DIR_PLUGINS . "$plugin/objects/";
		} elseif( $parts[0] == 'Difra' and $parts[1] == 'Plugins' ) {
			$name = strtolower( $parts[2] );
			// классы вида Plugins/Name ищем в plugins/name/lib/name.php
			if( sizeof( $parts ) == 3 ) {
				$parts[] = $name;
			}
			$parts = array_slice( $parts, 3 );
			$path = DIR_PLUGINS . "$name/lib/";
		} else {
			$path = defined( 'DIR_FW' ) ? DIR_FW . 'lib/' : __DIR__ . '/';
			array_shift( $parts );
		}
		return $path . strtolower( implode( '/', $parts ) ) . '.php';
	}

	/**
	 * Обработчик событий
	 * @throws exception
	 */
	public static function register() {

		spl_autoload_register( 'Difra\Autoloader::load' );
	}

	/**
	 * Добавляет имя класса в чёрный список
	 * @param string $class
	 */
	public static function addBL( $class ) {

		$lClass = strtolower( trim( $class, '\\' ) );
		if( !in_array( $lClass, self::$bl ) ) {
			self::$bl[] = $lClass;
		}
	}
}

// Регистрация обработчика событий
Autoloader::register();
