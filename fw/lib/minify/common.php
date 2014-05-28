<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Minify;

/**
 * Абстрактный класс для минификаторов
 */
abstract class Common {

	/** Функция для минификации данных */
	abstract function minify( $data );

	/**
	 * Синглтон
	 *
	 * @static
	 * @return self
	 */
	static public function getInstance() {

		static $_instances = array();
		$name = get_called_class();
		return isset( $_instances[$name] ) ? $_instances[$name] : $_instances[$name] = new $name;
	}
}