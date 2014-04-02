<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

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