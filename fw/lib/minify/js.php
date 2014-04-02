<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Minify;

/**
 * Class JS
 *
 * @package Difra\Minify
 */
class JS extends Common {

	/**
	 * Minify JavaScript
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public function minify( $data ) {

		return JS\JSMin::minify( $data );
	}
}
	
