<?php

namespace Difra\Minify;

/**
 * Minification abstract adapter
 */
abstract class Common {

	/**
	 * Singleton
	 *
*@static
	 * @return self
	 */
	static public function getInstance() {

		static $_instances = [];
		$name = get_called_class();
		return isset( $_instances[$name] ) ? $_instances[$name] : $_instances[$name] = new $name;
	}

	/**
	 * Minification method
	 *
	 * @param $data
	 * @return mixed
	 */
	abstract function minify($data);
}