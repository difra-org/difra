<?php

namespace Difra\Param;

/**
 * Class Common
 *
 * @package Difra\Param
 */
abstract class Common {

	const type = null;
	const source = null;
	const named = null;
	const auto = false;

	protected $value = null;

	public function __construct( $value = '' ) {

		switch( static::type ) {
		case 'file':
			$this->value = $value;
			return;
		case 'files':
			$files = array();
			foreach( $value as $file ) {
				if( $file['error'] == UPLOAD_ERR_OK ) {
					$files[] = new AjaxFile( $file );
				}
			}
			$this->value = $files;
			return;
		case 'data':
			$this->value = $value;
			return;
		}
		$this->value = self::canonicalize( $value );
		switch( static::type ) {
		case 'string':
			$this->value = (string)$value;
			break;
		case 'int':
			$this->value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
			break;
		case 'float':
			$value = str_replace( ',', '.', $value );
			$this->value = filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			break;
		case 'url':
			// TODO: заменить этот фильтр на ESAPI
			$this->value = filter_var( $value, FILTER_SANITIZE_URL );
			break;
		case 'email':
			// TODO: заменить этот фильтр на ESAPI
			$this->value = filter_var( $value, FILTER_SANITIZE_EMAIL );
			break;
		case 'ip':
			$this->value = filter_var( $value, FILTER_VALIDATE_IP ) ? $value : null;
			break;
		default:
			throw new \Difra\Exception( 'No wrapper for type ' . ( static::type ) . ' in Param\Common constructor.' );
		}
	}

	public function __toString() {

		$value = $this->val();
		if( is_array( $value ) ) {
			return '';
		}
		return (string)$this->val();
	}

	public static function verify( $value ) {

		switch( static::type ) {
		case 'file':
			if( !isset( $value['error'] ) or $value['error'] !== UPLOAD_ERR_OK ) {
				return false;
			}
			return true;
		case 'files':
			if( !is_array( $value ) or empty( $value ) ) {
				return false;
			}
			foreach( $value as $fileData ) {
				if( $fileData['error'] === UPLOAD_ERR_OK ) {
					return true;
				}
			}
			return false;
		case 'data':
			return true;
		}
		if( is_array( $value ) or is_object( $value ) ) {
			return false;
		}
		$value = self::canonicalize( $value );
		switch( static::type ) {
		case 'string':
			return is_string( $value );
		case 'int':
			return filter_var( $value, FILTER_VALIDATE_INT ) or $value === '0';
		case 'float':
			$value = str_replace( ',', '.', $value );
			return ( false !== filter_var( $value, FILTER_VALIDATE_FLOAT ) ) ? true : false;
		case 'url':
			// TODO: заменить этот фильтр на ESAPI
			return filter_var( $value, FILTER_VALIDATE_URL );
		case 'email':
			// TODO: заменить этот фильтр на ESAPI
			return ( false !== filter_var( $value, FILTER_VALIDATE_EMAIL ) ) ? true : false;
		case 'ip':
			return filter_var( $value, FILTER_VALIDATE_IP );
		default:
			throw new \Difra\Exception( 'Can\'t check param of type: ' . static::type );
		}
	}

	/**
	 * @param $str
	 *
	 * @return string|null
	 */
	static function canonicalize( $str ) {

		try {
			return @\Difra\Libs\ESAPI::encoder()->canonicalize( $str );
		} catch( \Exception $ex ) {
			return null;
		}
	}

	public function val() {

		switch( static::type ) {
		case 'file':
			if( $this->value['error'] === UPLOAD_ERR_OK ) {
				return file_get_contents( $this->value['tmp_name'] );
			}
			return null;
		case 'files':
			$res = array();
			foreach( $this->value as $file ) {
				$res[] = $file->val();
			}
			return $res;
		default:
			return $this->value;
		}
	}

	public function raw() {

		return $this->value;
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