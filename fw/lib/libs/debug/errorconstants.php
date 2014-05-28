<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Libs\Debug;

/**
 * Class errorConstants
 *
 * @package Difra\Libs\Debug
 */
class errorConstants {

	private $errors = array(
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_CORE_ERROR => 'E_COMPILE_ERROR',
		E_CORE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
		E_STRICT => 'E_STRICT',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED => 'E_DEPRECATED',
		E_USER_DEPRECATED => 'E_USER_DEPRECATED'
	);

	/**
	 * Синглтон
	 *
	 * @static
	 * @return errorConstants
	 */
	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	/**
	 * Возвращает массив соответствий кодов ошибок и соответствующих строк
	 *
	 * @return array
	 */
	public function getArray() {

		return $this->errors;
	}

	/**
	 * Возвращает по коду ошибки строку в виде "E_COMPILE_ERROR"
	 *
	 * @param $code
	 *
	 * @return null
	 */
	public function getError( $code ) {

		return isset( $this->errors[$code] ) ? $this->errors[$code] : null;
	}

	/**
	 * Возвращает по коду ошибки строку в виде "Compile Error"
	 *
	 * @param $code
	 *
	 * @return null|string
	 */
	public function getVerbalError( $code ) {

		if( !isset( $this->errors[$code] ) ) {
			return null;
		}
		$error = $this->errors[$code];
		if( substr( $error, 0, 2 ) == 'E_' ) {
			$error = substr( $error, 2 );
		}
		return ucwords( strtolower( str_replace( '_', ' ', $error ) ) );
	}
}
