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
		if( !empty( $_SERVER['HTTP_HOST'] ) ) {
			return $_SERVER['HTTP_HOST'];
		}
		return gethostname();
	}

	/**
	 * Возвращает текущий URI
	 *
	 * @throws Exception
	 * @return string
	 */
	public static function getUri() {

		static $uri = null;
		if( !is_null( $uri ) ) {
			return $uri;
		}
		if( !empty( $_SERVER['URI'] ) ) { // это для редиректов запросов из nginx
			$uri = $_SERVER['URI'];
		} elseif( !empty( $_SERVER['REQUEST_URI'] ) ) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			throw new Exception( 'Can\'t get URI' );
		}
		if( false !== strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, strpos( $uri, '?' ) );
		}
		$uri = '/' . trim( $uri, '/' );
		return $uri;
	}

}