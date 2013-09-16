<?php

namespace Difra;

/**
 * Class Envi
 *
 * @package Difra
 */
class Envi {

	/**
	 * Режим работы (web, cli, include)

	 */

	/** @var string Режим работы (web, cli, include) */
	protected static $mode = 'include';

	/** Установить режим работы */
	public static function setMode( $mode ) {

		self::$mode = $mode;
	}

	/** Получить режим работы */
	public static function getMode() {

		return self::$mode;
	}

	/**
	 * Домен и подсайты
	 *
	 */

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
	 * Определяет имя папки в sites в следующем порядке:
	 * 1. Переменная VHOST_NAME, передаваемая от сервера.
	 * 2. Имя хоста в по алгоритму sub.subdomain.domain.com www.sub.subdomain.domain.com subdomain.domain.com
	 *    www.subdomain.domain.com domain.com www.domain.com.
	 * 3. "default".
	 *
	 * @return string|bool
	 */
	public static function getSite() {

		static $site = null;
		if( !is_null( $site ) ) {
			return $site;
		}

		// default behavior: site is defined by web server
		if( !empty( $_SERVER['VHOST_NAME'] ) ) {
			return $site = $_SERVER['VHOST_NAME'];
		}

		// no host name is available (most likely environment is not web server)
		if( !$host = self::getHost() ) {
			return $site = 'default';
		}

		// automatic behavior: try to compare host name to existing directories in sites folder
		$sitesLocation = __DIR__ . '/../../sites/';
		while( $host ) {
			if( is_dir( $sitesLocation . $host ) ) {
				return $site = $host;
			}
			if( is_dir( $sitesLocation . 'www.' . $host ) ) {
				return $site = 'www.' . $host;
			}
			$host = explode( '.', $host, 2 );
			$host = !empty( $host[1] ) ? $host[1] : false;
		}
		return $site = 'default';
	}

	/**
	 * URI

	 */

	/** @var string|null Кастомный URI (в основном, для тестов) */
	private static $customUri = null;
	/** @var string|null Определённый и почищенный URI */
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
			return null;
		}
		if( false !== strpos( self::$requestedUri, '?' ) ) {
			self::$requestedUri = substr( self::$requestedUri, 0, strpos( self::$requestedUri, '?' ) );
		}
		self::$requestedUri = '/' . trim( self::$requestedUri, '/' );
		self::$requestedUri = urldecode( self::$requestedUri );
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

	/**
	 * Environment data getters

	 */

	/**
	 * Возвращает текущие настройки в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public static function getConfigXML( $node ) {

		$config = self::getConfig();
		foreach( $config as $k => $v ) {
			$node->setAttribute( $k, $v );
		}
	}

	/**
	 * Возвращает массив текущих настроек
	 */
	public static function getConfig() {

		return array(
			'locale' => Envi\Setup::getLocale(),
			'host' => self::getSite(),
			'hostname' => self::getHost(),
			'mainhost' => self::getHost( true )
		);
	}
}