<?php

namespace Difra\View;

/**
 * Class Old
 *
 * @package Difra\View
 * @deprecated
 */
class Old {

	/**
	 * Синглтон
	 * @return self
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function httpError( $err, $ttl = false, $message = null ) {

		if( $ttl ) {
			self::addExpires( $ttl );
		}
		throw new Exception( $err, $message );
	}

	public function render( &$xml, $specificInstance = false, $dontEcho = false ) {

		return \Difra\View::render( $xml, $specificInstance, $dontEcho );
	}

	public function redirect( $url ) {

		\Difra\View::redirect( $url );
	}

	public static function addExpires( $ttl ) {

		\Difra\View::addExpires( $ttl );
	}

	public static function normalize( $htmlDoc ) {

		return \Difra\View::normalize( $htmlDoc );
	}

	public function __set( $name, $value ) {

		\Difra\View::${$name} = $value;
	}

	public function __get( $name ) {

		return \Difra\View::${$name};
	}
}