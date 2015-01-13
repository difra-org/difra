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

	 */

	/**
	 * Получить имя хоста (домена)
	 *
	 * @param bool $main Получить имя «главного» хоста (нужно в случае, если у сайта есть поддомены)
	 *
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
		$sitesLocation = DIR_ROOT . 'sites/';
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
	/** @var string|null Определённый и почищенный URI без urldecode() */
	private static $requestedUriRaw = null;

	/**
	 * Возвращает текущий URI
	 *
	 * @param bool $raw Не делать urldecode
	 *
	 * @return string
	 */
	public static function getUri( $raw = false ) {

		if( is_null( self::$requestedUri ) ) {
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
			self::$requestedUriRaw = '/' . trim( self::$requestedUri, '/' );

			self::$requestedUri = urldecode( self::$requestedUriRaw );
		}
		return $raw ? self::$requestedUriRaw : self::$requestedUri;
	}

	/**
	 * Устанавливает текущий URI
	 *
	 * @param string $uri
	 */
	public static function setUri( $uri ) {

		self::$customUri = $uri;
		self::$requestedUri = null;
		self::$requestedUriRaw = null;
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

	public static function getDomainProperties() {

		$returnArray = array(
			'domain' => self::getHost( true ),
			'site' => self::getSite(),
			'devmode' => \Difra\Debugger::isEnabled(),
			'plugins' => array_keys( \Difra\Plugger::getAllPlugins() )
		);

		return $returnArray;
	}

	public static function checkPlugins( $pluginsList ) {

		if( isset( $pluginsList['plugins'] ) && $pluginsList['plugins'] !='' ) {

			$plist = explode( ',', $pluginsList['plugins'] );
			$plist = array_map( 'trim', $plist );
			$cPlist = array_keys( Plugger::getAllPlugins() );
			$checkResult = array_diff( $cPlist, $plist );
			if( !empty( $checkResult ) ) {
				\Difra\Adm\Localemanage::exitLocale();
			}
		}
	}

	public static function checkDomains( $domainList ) {

		if( !isset( $domainList['domains'] ) || empty( $domainList['domains'] ) ) {
			\Difra\Adm\Localemanage::exitLocale();
		}

		$curHost = self::getHost( true );
		$curDev = Debugger::isEnabled();
		$dTodList = array();

		if( !is_array( $domainList['domains'] ) ) {
			if( $domainList['domains'] != '*' ) {
				\Difra\Adm\Localemanage::exitLocale();
			}
			return;
		}

		foreach( $domainList['domains'] as $k=>$dData ) {
			$dTodList[$dData['domain']] = $dData['devmode'];
		}
		if( !array_key_exists( $curHost, $dTodList ) ) {
			\Difra\Adm\Localemanage::exitLocale();
		}
		if( $dTodList[$curHost] == 0 ) {
			if( $curDev > 0 ) {
				\Difra\Adm\Localemanage::exitLocale();
			}
		}
	}

	public static function makeEnviLocale( $localeString ) {

		$localeString = base64_encode( serialize( $localeString ) );
		$flName = base64_encode( 'difra_license_file.lic' );

		Cache::getInstance()->put( 'difraLocales', $localeString, 1209600 );
		$zuss = @file_put_contents( DIR_DATA . $flName, $localeString );
		if( $zuss === false ) {
			throw new \Difra\View\Exception( 'data/ is not writeable' );
			return;
		}
	}

}