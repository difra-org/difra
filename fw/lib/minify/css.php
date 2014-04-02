<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Minify;

/**
 * Класс для минификации CSS
 * Использование: \Difra\Minify\CSS::getInstance()->minify( $css )
 */
class CSS extends Common {

	/**
	 * Минификация CSS
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public function minify( $data ) {

//		$data = preg_replace( '/\/\*.*?\*\//s', '', $data ); // remove comments
//		$data = preg_replace( '/\s+/', ' ', $data ); // remove replace multiple whitespaces with space
//		$data = preg_replace( '/\s?([{};:,])\s/', '$1', $data ); // remove spaces near some symbols
		return $data;
	}
}

