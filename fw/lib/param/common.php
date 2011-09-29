<?php

namespace Difra\Param;

class Common {

	protected $value = null;

	public function __construct( $value = '' ) {

		switch( static::type ) {
		case 'string':
			$this->value = (string) $value;
			break;
		case 'int':
			$this->value = (int) $value;
			break;
		default:
			throw new \Difra\Exception( 'No wrapper for type ' . ( static::type ) . ' in Param\Common constructor.' );
		}
	}

	public function __toString() {

		return (string)$this->value;
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

	public function val() {

		return $this->value;
	}

	public static function getSource() {

		return static::source;
	}

	public static function isNamed() {

		return static::named;
	}
}