<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra;

/**
 * Class Minify
 *
 * @package Difra
 */
class Minify {

	/**
	 * @param string $type
	 *
	 * @return \Difra\Minify\Common
	 */
	static public function getInstance( $type ) {

		switch( $type ) {
		case 'css':
			return Minify\CSS::getInstance();
		case 'js':
			return Minify\JS::getInstance();
		default:
			return Minify\None::getInstance();
		}
	}
}
