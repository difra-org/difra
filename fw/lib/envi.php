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

	private static $customUri = null;
	private static $requestedUri = null;

	/**
	 * Возвращает текущий URI
	 *
	 * @throws Exception
	 * @return string
	 */
	public static function getUri() {

		if( !is_null( self::$requestedUri ) ) {
			return self::$requestedUri;
		}
		if( !is_null( self::$customUri ) ) {
			self::$requestedUri = self::$customUri;
		} elseif( !empty( $_SERVER['URI'] ) ) { // это для редиректов запросов из nginx
			self::$requestedUri = $_SERVER['URI'];
		} elseif( !empty( $_SERVER['REQUEST_URI'] ) ) {
			self::$requestedUri = $_SERVER['REQUEST_URI'];
		} else {
			throw new Exception( 'Can\'t get URI' );
		}
		if( false !== strpos( self::$requestedUri, '?' ) ) {
			self::$requestedUri = substr( self::$requestedUri, 0, strpos( self::$requestedUri, '?' ) );
		}
		self::$requestedUri = '/' . trim( self::$requestedUri, '/' );
		return self::$requestedUri;
	}

	/**
	 * Устанавливает текущий URI
	 *
	 * @param string $uri
	 */
	public static function setUri( $uri ) {

		self::$customUri = $uri;
		self::$requestedUri = null;
	}
}