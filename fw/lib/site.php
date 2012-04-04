<?php

namespace Difra;

class Site {

	const VERSION = '2.0';
	const BUILD = '$Rev$';

	const PATH_PART = '/../../sites/';

	// libs
	private $locale = 'ru_RU';

	private $siteDir = null;
	private $siteConfig = array();
	private $host = null;

	private $phpVersion = null;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		$this->detectHost();
		if( is_file( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' ) ) {
			$this->siteConfig = include( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' );
		}
		$this->configureLocale();
		$this->configurePHP();
		$this->configurePaths();
		$this->sessionLoad();

		Events::register( 'core-init', 'Difra\\Debugger' );
		Events::register( 'plugins-load', 'Difra\Plugger' );
		Events::register( 'action-find', 'Difra\\Action', 'find' );
		Events::register( 'action-run', 'Difra\\Action', 'run' );
		Events::register( 'render-run', 'Difra\\Action', 'render' );
	}

	public function __destruct() {

		$this->sessionSave();
	}

	public function detectHost() {

		$sitesDir = dirname( __FILE__ ) . self::PATH_PART;
		
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
		// не нашли подходящий хост. ставим по умолчанию — default
		if( !$this->host ) {
			$this->host = 'default';
		}

		// ищем папку сайта
		if( is_dir( $sitesDir . $this->host ) ) {
			$this->siteDir = $this->host;
		} elseif( is_dir( $sitesDir . 'www.' . $this->host ) ) {
			$this->siteDir = 'www.' . $this->host;
		} else {
			header( 'HTTP/1.1 500 Internal Server Error' );
			die( 'Internal server error: difra can not find the configuration.' );
		}
				
		return true;
	}

	public function configurePaths() {

		$_SERVER['DOCUMENT_ROOT'] = realpath( dirname( __FILE__ ) . self::PATH_PART . '..' ) . '/';
		define( 'DIR_ROOT', $_SERVER['DOCUMENT_ROOT'] );
		define( 'DIR_FW', $_SERVER['DOCUMENT_ROOT'] . 'fw/' );
		define( 'DIR_SITE', $_SERVER['DOCUMENT_ROOT'] . 'sites/' . $this->siteDir . '/' );
		define( 'DIR_PLUGINS', $_SERVER['DOCUMENT_ROOT'] . 'plugins/' );
		define( 'DIR_HTDOCS', DIR_SITE . 'htdocs/' );
		define( 'DIR_DATA', DIR_SITE . 'data/' );
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

		// prepare data
		$this->_stripSlashes();
	}

	/**
	 * Убирает слеши из $_GET, $_POST, $_COOKIE, $_REQUEST, если включены magic quotes
	 * @return
	 */
	private function _stripSlashes() {

		if( get_magic_quotes_gpc() != 1 ) {
			return;
		}
		$strip_slashes_deep = function ( $value ) use ( &$strip_slashes_deep ) {
			return is_array( $value ) ? array_map( $strip_slashes_deep, $value ) : stripslashes( $value );
		};
		$_GET               = array_map( $strip_slashes_deep, $_GET );
		$_POST              = array_map( $strip_slashes_deep, $_POST );
		$_COOKIE            = array_map( $strip_slashes_deep, $_COOKIE );
	}

	public function getDbConfig() {

		return isset( $this->siteConfig['db'] ) ? $this->siteConfig['db'] : array( 'username' => '', 'password' => '', 'database' => '' );
	}

	public function getLocale() {

		return $this->locale;
	}

	public function getLocaleObj( $locale = null ) {

		return Locales::getInstance( $locale );
	}

	public function configureLocale() {

		if( isset( $this->siteConfig['locale'] ) ) {
			$this->locale = $this->siteConfig['locale'];
		}
	}

	public function getHostname() {

		if( !empty( $_SERVER['HTTP_HOST'] ) ) {
			return $_SERVER['HTTP_HOST'];
		} else {
			return null;
		}
	}

	public function getMainhost() {

		return !empty( $_SERVER['VHOST_MAIN'] ) ? $_SERVER['VHOST_MAIN'] : $this->getHostname();
	}

	public function getHost() {

		return $this->host;
	}

	public function getBuild( $asArray = false ) {
	
		static $_build = null;
		static $_array = null;

		if( !$asArray and !is_null( $_build ) ) {
			return $_build;
		} elseif( $asArray and !is_null( $_array ) ) {
			return $_array;
		}
		
		// fw version and build
		$svnVer = array();
		if( is_file( DIR_FW . '.svn/entries' ) ) {
			$svn = file( DIR_FW . '.svn/entries' );
			$svnVer[] = self::VERSION . '.' . trim( $svn[3] );
		} elseif( preg_match( '/\d+/', self::BUILD, $match ) ) {
			$svnVer[] = self::VERSION . '.' . $match[0];
		} else {
			$svnVer[] = self::VERSION;
		}
		// plugins builds summary
		$list = Plugger::getInstance()->getList();
		$plugVer = 0;
		foreach( $list as $name ) {
			if( is_file( DIR_PLUGINS . "$name/.svn/entries" ) ) {
				$svn = file( DIR_PLUGINS . "$name/.svn/entries" );
				$plugVer += trim( $svn[3] );
			}
		}
		if( $plugVer ) {
			$svnVer[] = $plugVer;
		}
		// site revision
		if( is_file( DIR_SITE . '.svn/entries' ) ) {
			$svn = file( DIR_SITE . '.svn/entries' );
			$svnVer[] = trim( $svn[3] );
		}

		$_array = $svnVer;
		if( $asArray ) {
			return $svnVer;
		} elseif( !empty( $svnVer ) ) {
			return $_build = implode( '.', $svnVer );
		}

		return $_build = '-';
	}

	/**
	 * Возвращает текущие настройки в XML
	 * @param \DOMNode $node
	 */
	public function getConfigXML( $node ) {

		$node->setAttribute( 'locale', $this->locale );
		$node->setAttribute( 'host', $this->getHost() );
		$node->setAttribute( 'hostname', $this->getHostname() );
		$node->setAttribute( 'mainhost', $this->getMainhost() );
	}

	public function getData( $key ) {

		return isset( $this->siteConfig[$key] ) ? $this->siteConfig[$key] : false;
	}

	public function sessionLoad() {

		if( !isset( $_SESSION ) and isset( $_COOKIE[ini_get( 'session.name' )] ) ) {
			session_start();
			if( !isset( $_SESSION['dhost'] ) or $_SESSION['dhost'] != $this->getHost() ) {
				$_SESSION = array();
			}
		}
	}

	public function sessionStart() {

		$this->sessionLoad();
		if( !isset( $_SESSION ) ) {
			session_start();
			$_SESSION = array();
			$_SESSION['dhost'] = $this->getHost();
		}
	}

	public function sessionSave() {

		if( !empty( $_SESSION ) and empty( $_SESSION['dhost'] ) ) {
			$_SESSION['dhost'] = $this->getHost();
		}
	}
}
