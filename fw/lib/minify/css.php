<?php

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

		$data = preg_replace( '/\/\*.*?\*\//s', '', $data ); // remove comments
		$data = preg_replace( '/\s+/', ' ', $data ); // remove replace multiple whitespaces with space
		$data = preg_replace( '/\s?([{};:,])\s/', '$1', $data ); // remove spaces near some symbols
		return $data;
	}
}

