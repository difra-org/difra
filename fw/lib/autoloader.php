<?php

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

		$fileName = str_replace( '_', '/', strtolower( $class ) ) . '.php';
		if( !$filePath = self::getInstance()->search( $fileName ) ) {
			throw new exception( 'Can\'t find class ' . $class );
		}
		include_once( $filePath );
	}

	public static function register() {

		if( function_exists( 'spl_autoload_register' ) ) {
			if( !spl_autoload_register( 'Autoloader::load' ) ) {
				throw new exception( 'Can\'t register Autoloader' );
			}
		}
	}
}

// for PHP 5.3 and newer
Autoloader::register();

// for older PHP, will not be magic after spl_autoload_register if PHP version >= 5.3
function __autoload( $class ) {

	Autoloader::getInstance()->load( $class );
}

