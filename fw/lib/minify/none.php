<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Minify;

/**
 * Класс-заглушка для минификации
 */
class None extends Common {

	/**
	 * Функция-заглушка для минификации
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public function minify( $data ) {

		return $data;
	}
}
