<?php

namespace Difra;

/**
 * Class Envi
 *
 * @package Difra
 */
class Envi {

	/** @var string Режим работы (web, cli, include) */
	static protected $mode = 'include';

	/** Установить режим работы */
	public static function setMode( $mode ) {

		self::$mode = $mode;
	}

	/** Получить режим работы */
	public static function getMode() {

		return self::$mode;
	}

	/**
	 * Получить имя хоста (домена)
	 * @param bool $main        Получить имя «главного» хоста (нужно в случае, если у сайта есть поддомены)
	 * @return string
	 */
	public static function getHost( $main = false ) {

		if( $main and !empty( $_SERVER['VHOST_MAIN'] ) ) {
			return $_SERVER['VHOST_MAIN'];
		}
		return $_SERVER['HTTP_HOST'];
	}
}