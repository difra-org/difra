<?php

namespace Difra\Param\Traits;

/**
 * Class HTML
 *
 * @package Difra\Param\Traits
 */
trait HTML {

	private $savedImages = false;
	private $raw = '';

	/**
	 * Проверка значения
	 * @param string $value
	 * @return string
	 */
	public static function verify( $value ) {

		return trim( $value );
	}

	/**
	 * Сохранение изображений
	 * @param string $path
	 * @param string $urlPrefix
	 */
	public function saveImages( $path, $urlPrefix ) {

		\Difra\Libs\Vault::saveImages( $this->value, $path, $urlPrefix );
		$this->savedImages = true;
	}

	/**
	 * Получение «причёсанного» html
	 *
	 * @param bool $quiet
	 * @return string
	 */
	public function val( $quiet = false ) {

		if( !$quiet and !$this->savedImages ) {
			trigger_error(
				"HTML val() called before saveImages() and \$quiet parameter is not set. Is that really what you want?",
				E_USER_NOTICE
			);
		}
		return $this->value;
	}

	/**
	 * Получение html в оригинальном виде
	 *
	 * @return string
	 */
	function raw() {

		return $this->raw;
	}
}