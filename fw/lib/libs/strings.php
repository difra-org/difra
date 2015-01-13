<?php

namespace Difra\Libs;

/**
 * Class Strings
 *
 * @package Difra\Libs
 */
class Strings {

	/**
	 * Определяет, является ли символ пробельным
	 *
	 * @param string $char
	 *
	 * @return bool
	 */
	public static function isWhitespace( $char ) {

		switch( $char ) {
		case "\n":
		case "\r":
		case "\t":
		case ' ':
			return true;
		}
		return false;
	}
}