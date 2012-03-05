<?php

namespace Difra\Param;

abstract class Common {

	const type = null;
	const source = null;
	const named = null;
	const auto = false;

	protected $value = null;

	public function __construct( $value = '' ) {

		switch( static::type ) {
		case 'string':
			$this->value = (string) $value;
			break;
		case 'int':
			$this->value = (int) $value;
			break;
		case 'file':
			$this->value = $value;
			if( !$this->value['error'] ) {
				$this->value['content'] = file_get_contents( $this->value['tmp_name'] );
			}
			break;
		case 'float':
			$this->value = (float) $value;
			break;
		case 'data':
			$this->value = $value;
			break;
		default:
			throw new \Difra\Exception( 'No wrapper for type ' . ( static::type ) . ' in Param\Common constructor.' );
		}
	}

	public function __toString() {

		return (string)$this->val();
	}

	public static function verify( $value ) {

		switch( static::type ) {
		case 'string':
			return true;
		case 'int':
			return ctype_digit( $value );
		case 'file':
			if( $value['error'] ) {
				return false;
			}
			return true;
		case 'float':
			return is_numeric( $value );
		case 'data':
			return true;
		default:
			throw new \Difra\Exception( 'Can\'t check param of type: ' . static::type );
		}
	}

	public function val() {

		switch( static::type ) {
		case 'file':
			return $this->value['content'];
		default:
			return $this->value;
		}
	}

	public static function getSource() {

		return static::source;
	}

	public static function isNamed() {

		return static::named;
	}

	public static function isAuto() {

		return defined( 'static::auto' ) ? static::auto : false;
	}
}