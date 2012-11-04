<?php

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
