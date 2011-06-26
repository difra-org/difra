<?php

namespace Difra;

class Autoloader {

	private $basePath = '';
	private $paths = array();

	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		$this->basePath = realpath( dirname( __FILE__ ) . '/../..' );
		$this->paths[] = 'fw/lib';
		$this->paths[] = 'lib';
	}

	public function search( $name ) {

		
		foreach( $this->paths as $path ) {
			if( file_exists( "{$this->basePath}/$path/$name" ) ) {
				return "{$this->basePath}/$path/$name";
			}
		}
		return false;
	}

	public static function load( $class ) {

		$class = ltrim( $class, '\\' );
		$parts = explode( '\\', $class );
		$path = '';
		if( $parts[0] != 'Difra' ) {
			$path = 'lib/';
		} elseif( $parts[0] == 'Difra' and $parts[1] == 'Plugins' ) {
			$path = '';
			array_shift( $parts );
			$name = array_pop( $parts );
			// классы вида Plugins/Name ищем в plugins/name/lib/name.php
			if( sizeof( $parts ) == 1 ) {
				array_push( $parts, $name );
			}
			array_push( $parts, 'lib' );
			array_push( $parts, $name );
		} else {
			$path = 'fw/lib/';
			array_shift( $parts );
		}
		$filename = realpath( dirname( __FILE__ ) . '/../..' ) . "/$path" . strtolower( implode( '/', $parts ) ) . '.php';
		if( !file_exists( $filename ) ) {
			throw new exception( "Class $class not found" );
		}
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
