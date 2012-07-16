<?php

namespace Difra;

class Autoloader {

	static $bl = array( 'sqlite3' );

	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {
	}

	public static function load( $class ) {

		if( in_array( strtolower( trim( $class, '\\' ) ), self::$bl ) ) {
			return;
		}
		static $basePath = null;
		$basePath ? : $basePath = realpath( dirname( __FILE__ ) . '/../..' );
		$class = ltrim( $class, '\\' );
		$parts = explode( '\\', $class );
		if( $parts[0] != 'Difra' ) {
			$path = 'lib/';
		} elseif( $parts[0] == 'Difra' and $parts[1] == 'Plugins' ) {
			$name = $parts[2];
			// классы вида Plugins/Name ищем в plugins/name/lib/name.php
			if( sizeof( $parts ) == 3 ) {
				$parts = array();
				$path = "plugins/$name/lib/$name";
			} else {
				$parts = array_slice( $parts, 3 );
				$path = "plugins/$name/lib/";
			}
		} else {
			$path = 'fw/lib/';
			array_shift( $parts );
		}
		$filename = $basePath . strtolower( "/$path" . implode( '/', $parts ) ) . '.php';
/*		if( !is_file( $filename ) ) {
			throw new exception( "Class $class not found" );
		}*/
		include_once( $filename );
	}

	public static function register() {

		if( function_exists( 'spl_autoload_register' ) ) {
			if( !spl_autoload_register( 'Difra\Autoloader::load' ) ) {
				throw new exception( 'Can\'t register Autoloader' );
			}
		}
	}
}

Autoloader::register();
