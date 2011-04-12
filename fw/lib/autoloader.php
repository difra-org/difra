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

	public function load( $class ) {

		$fileName = str_replace( '_', '/', strtolower( $class ) ) . '.php';
		if( !$filePath = $this->search( $fileName ) ) {
			throw new exception( 'Can\'t find class ' . $class );
		}
		include_once( $filePath );
	}
}

function __autoload( $class ) {

	Autoloader::getInstance()->load( $class );
}

