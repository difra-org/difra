<?php

namespace Difra;

/**
 * Class Site
 *
 * @package Difra
 * @deprecated
 * Устаревший класс, так как в оригинале он использовался для вызова компонентов движка, теперь для этого используется Events.
 */
class Site {

	const VERSION = '5.0';
	const BUILD = '$Rev$';
	const PATH_PART = '/../../';

	private $locale = 'ru_RU';

	private $siteDir = null;
	private $host = null;

	private $phpVersion = null;

	static $siteInit = false;

	/**
	 * Синглтон
	 * @return self
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Инициализация окружения
	 */
	public function init() {

		static $initDone = false;
		self::$siteInit = true;
		if( $initDone ) {
			return;
		}
		$this->detectHost();
		$this->configurePaths();
		$this->configureLocale();
		$this->configurePHP();
		$this->sessionLoad();
		//header( 'X-Powered-By: Difra' );
		View::addExpires( 0 );
		$initDone = true;
	}

	public function initDone() {

		self::$siteInit = false;
	}

	/**
	 * Возвращает true, если в данный момент происходит инициализация сайта
	 *
	 * @return bool
	 */
	public static function isInit() {

		return self::$siteInit;
	}

	public function __destruct() {

		$this->sessionSave();
	}

	/**
	 * Определяет имя папки в sites в следующем порядке:
	 * 1. Переменная VHOST_NAME, передаваемая от сервера.
	 * 2. Имя хоста в по алгоритму sub.subdomain.domain.com www.sub.subdomain.domain.com subdomain.domain.com
	 *    www.subdomain.domain.com domain.com www.domain.com.
	 * 3. "default".
	 *
	 * @return bool
	 */
	private function detectHost() {

		$sitesDir = __DIR__ . self::PATH_PART . 'sites';

		// хост передаётся от веб-сервера
		if( !empty( $_SERVER['VHOST_NAME'] ) ) {
			$this->host = $_SERVER['VHOST_NAME'];
			// определяем хост по hostname
		} elseif( $host = $this->getHostname() ) {
			while( $host ) {
				if( is_dir( $sitesDir . $host ) or is_dir( $sitesDir . 'www.' . $host ) ) {
					$this->host = $host;
					break;
				}
				$host = explode( '.', $host, 2 );
				$host = !empty( $host[1] ) ? $host[1] : false;
			}
		}
		$this->siteDir = $this->host;
		if( !is_dir( $sitesDir . $this->host ) and is_dir( $sitesDir . 'www.' . $this->host ) ) {
			$this->siteDir = 'www.' . $this->host;
		}

		// не нашли подходящий хост. ставим по умолчанию — default
		if( !$this->host ) {
			$this->host = 'default';
		}

		return true;
	}

	public function configurePaths() {

		if( !defined( 'DIR_ROOT' ) ) {
			define( 'DIR_ROOT', __DIR__ . self::PATH_PART );
		}
		$_SERVER['DOCUMENT_ROOT'] = DIR_ROOT;
		define( 'DIR_FW', ( defined( 'DIR_PHAR' ) ? DIR_PHAR : DIR_ROOT ) . 'fw/' );
		define( 'DIR_SITE', DIR_ROOT . 'sites/' . $this->siteDir . '/' );
		define( 'DIR_PLUGINS', ( defined( 'DIR_PHAR' ) ? DIR_PHAR : DIR_ROOT ) . 'plugins/' );
		//define( 'DIR_HTDOCS', DIR_SITE . 'htdocs/' );
		define( 'DIR_DATA', !empty( $_SERVER['VHOST_DATA'] ) ? $_SERVER['VHOST_DATA'] . '/' : DIR_ROOT . 'data/' );
	}

	public function configurePHP() {

		// get php version
		$this->phpVersion = explode( '.', phpversion() );
		$this->phpVersion = $this->phpVersion[0] * 100 + $this->phpVersion[1];

		// other
		setlocale( LC_ALL, array( $this->locale . '.UTF-8', $this->locale . '.utf8' ) );
		setlocale( LC_NUMERIC, array( 'en_US.UTF-8', 'en_US.utf8' ) );
		mb_internal_encoding( 'UTF-8' );
		ini_set( 'short_open_tag', false );
		ini_set( 'asp_tags', false );
		ini_set( 'mysql.trace_mode', false );

		// set session domain
		ini_set( 'session.use_cookies', true );
		ini_set( 'session.use_only_cookies', true );
		ini_set( 'session.cookie_domain', '.' . $this->getMainhost() );

		// set default time zone
		if( !ini_get( 'date.timezone' ) ) {
			date_default_timezone_set( 'Europe/Moscow' );
		}

		// prepare data
		self::stripSlashes();
	}

	/**
	 * Убирает слеши из $_GET, $_POST, $_COOKIE, $_REQUEST, если включены magic quotes
	 *
	 * @return void
	 */
	private static function stripSlashes() {

		if( get_magic_quotes_gpc() != 1 ) {
			return;
		}
		$strip_slashes_deep = function ( $value ) use ( &$strip_slashes_deep ) {

			return is_array( $value ) ? array_map( $strip_slashes_deep, $value ) : stripslashes( $value );
		};
		$_GET = array_map( $strip_slashes_deep, $_GET );
		$_POST = array_map( $strip_slashes_deep, $_POST );
		$_COOKIE = array_map( $strip_slashes_deep, $_COOKIE );
	}

	/**
	 * Возвращает название локали
	 * @return string
	 */
	public function getLocale() {

		return $this->locale;
	}

	public function configureLocale() {

		if( $locale = Config::getInstance()->get( 'locale' ) ) {
			$this->locale = $locale;
		}
	}

	/**
	 * Возвращает имя сайта, которое определено в $this->detectHost()
	 *
	 * @return null
	 */
	public function getHost() {

		return $this->host;
	}

	/**
	 * Получить версию ревизии SVN
	 *
	 * @param string $dir Путь к папке со слэшем в конце
	 * @return int|bool
	 */
	private function getSVNRev( $dir ) {

		// try to get svn 1.7 revision
		if( class_exists( '\SQLite3' ) and is_readable( $dir . '.svn/wc.db' ) ) {
			try {
				$sqlite = new \SQLite3( $dir . '.svn/wc.db' );
				$res = $sqlite->query( 'SELECT MAX(revision) FROM `NODES`' );
				$res = $res->fetchArray();
				return $res[0];
			} catch( \Exception $ex ) {
			}
		} else { // try to get old svn revision
			if( is_file( $dir . '.svn/entries' ) ) {
				$svn = file( $dir . '.svn/entries' );
			}
			if( isset( $svn[3] ) ) {
				return trim( $svn[3] );
			}
		}
		return false;
	}

	/**
	 * Возвращает строку, условно обозначающую текущую ревизию
	 * @param bool $asArray
	 * @return array|string
	 */
	public function getBuild( $asArray = false ) {

		static $_build = null;
		static $_array = null;

		if( !$asArray and !is_null( $_build ) ) {
			return $_build;
		} elseif( $asArray and !is_null( $_array ) ) {
			return $_array;
		}

		$svnVer = array( self::VERSION );
		// fw version and build
		$fwVer = $this->getSVNRev( DIR_FW );
		if( $fwVer !== false ) {
			$svnVer[] = $fwVer;
		} elseif( preg_match( '/\d+/', self::BUILD, $match ) ) {
			$svnVer[] = $match[0];
		}
		// site revision
		$siteVer = $this->getSVNRev( DIR_ROOT );
		if( $siteVer !== false ) {
			$svnVer[] = $siteVer;
		}

		if( $asArray ) {
			return $svnVer;
		} elseif( !empty( $svnVer ) ) {
			return $_build = implode( '.', $svnVer );
		} else {
			return $_build = '-';
		}
	}

	/**
	 * Возвращает текущие настройки в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public function getConfigXML( $node ) {

		$config = $this->getConfig();
		foreach( $config as $k => $v ) {
			$node->setAttribute( $k, $v );
		}
	}

	/**
	 * Возвращает массив текущих настроек
	 */
	public function getConfig() {

		return array(
			'locale' => $this->locale,
			'host' => $this->getHost(),
			'hostname' => $this->getHostname(),
			'mainhost' => $this->getMainhost()
		);
	}

	public function sessionLoad() {

		if( !isset( $_SESSION ) and isset( $_COOKIE[ini_get( 'session.name' )] ) ) {
			session_start();
			if( !isset( $_SESSION['dhost'] ) or $_SESSION['dhost'] != $this->getMainhost() ) {
				$_SESSION = array();
			}
		}
	}

	public function sessionStart() {

		$this->sessionLoad();
		if( !isset( $_SESSION ) ) {
			session_start();
			$_SESSION = array();
			$_SESSION['dhost'] = $this->getMainhost();
		}
	}

	public function sessionSave() {

		if( !empty( $_SESSION ) and empty( $_SESSION['dhost'] ) ) {
			$_SESSION['dhost'] = $this->getMainhost();
		}
	}

	/**
	 * Методы, для которых готова замена
	 */

	/**
	 * Определяет имя хоста из URL
	 *
	 * @deprecated
	 * @return string|null
	 */
	public function getHostname() {

		return Envi::getHost();
	}

	/**
	 * Возвращает имя главного хоста, если он установлен в переменной веб-сервера VHOST_MAIN, либо имя текущего хоста
	 *
	 * @deprecated
	 * Envi::getHost( true ) делает то же саоме
	 * @return string
	 */
	public function getMainhost() {

		return Envi::getHost( true );
	}

}
