<?php

namespace Difra\Param;

class Common {

	private $value = null;

	public function __construct( $value = '' ) {

		$this->value = $value;
	}

	public function __toString() {

		return $this->value;
	}

	public static function verify( $value ) {

		switch( static::type ) {
		case 'string':
			return true;
		case 'int':
			return ctype_digit( $value );
		default:
			throw new \Difra\Exception( 'Can\'t check param of type: ' . static::type );
		}
	}

	public static function getSource() {

		return static::source;
	}

	public static function isNamed() {

		return static::named;
	}
}