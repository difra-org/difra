<?php

namespace Difra;

class Site {

	const PATH_PART = '/../../sites/';

	// libs
	private $locales = array();
	private $locale = 'ru_RU';

	private $siteDir = null;
	private $siteConfig = array();
	private $startTime;
	private $host = null;

	private $phpVersion = null;

	static public function getInstance( $reset = false ) {

		static $self = null;
		return ( $self and !$reset ) ? $self : $self = new self( );
	}

	public function __construct() {

		if( !$this->detectHost() ) {
			die( 'Invalid server configuration or unconfigured host.' );
		}
		$this->startTime = microtime( true );
		Debugger::getInstance();
		if( is_file( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' ) ) {
			$this->siteConfig = include ( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' );
		}
		$this->configurePHP();
		$this->configurePaths();
		$this->configureLocales();
	}

	private function detectHost() {

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
		} elseif( is_dir( $sitesDir . 'default' ) ) {
			$this->siteDir = 'default';
		} else {
			return false;
		}
				
		return true;
	}

	private function configurePaths() {

		$_SERVER['DOCUMENT_ROOT'] = realpath( dirname( __FILE__ ) . self::PATH_PART . '..' ) . '/';
		define( 'DIR_ROOT', $_SERVER['DOCUMENT_ROOT'] );
		define( 'DIR_FW', $_SERVER['DOCUMENT_ROOT'] . 'fw/' );
		define( 'DIR_SITE', $_SERVER['DOCUMENT_ROOT'] . 'sites/' . $this->siteDir . '/' );
		define( 'DIR_PLUGINS', $_SERVER['DOCUMENT_ROOT'] . 'plugins/' );
		define( 'DIR_HTDOCS', DIR_SITE . 'htdocs/' );
	}

	private function configurePHP() {

		// get php version
		$this->phpVersion = explode( '.', phpversion() );
		$this->phpVersion = $this->phpVersion[0] * 100 + $this->phpVersion[1];

		// other
		setlocale( LC_ALL, 'UTF8' );
		mb_internal_encoding( 'UTF-8' );
		ini_set( 'short_open_tag', false );
		ini_set( 'asp_tags', false );
		
		// set session domain
		ini_set( 'session.cookie_domain', '.' . $this->getMainhost() );
		
		// prepare data
		$this->_stripSlashes();
	}

	private function _stripSlashes() {

		if( get_magic_quotes_gpc() !== 1 ) {
			return false;
		}
		$_GET = json_decode( stripslashes( json_encode( $_GET, JSON_HEX_APOS ) ), true );
		$_POST = json_decode( stripslashes( json_encode( $_POST, JSON_HEX_APOS ) ), true );
		$_COOKIE = json_decode( stripslashes( json_encode( $_COOKIE, JSON_HEX_APOS ) ), true );
		$_REQUEST = json_decode( stripslashes( json_encode( $_REQUEST, JSON_HEX_APOS ) ), true );
	}

	public function getDbConfig() {

		return isset( $this->siteConfig['db'] ) ? $this->siteConfig['db'] : array( 'username' => '', 'password' => '', 'database' => '' );
	}

	public function getLocale() {

		return $this->locale;
	}

	public function getLocalesList() {

		return array_keys( $this->locales );
	}

	public function getLocalesListXML( $node ) {

		$data = $this->getLocalesList();
		if( !empty( $data ) ) {
			foreach( $data as $lang ) {
				$langNode = $node->appendChild( $node->ownerDocument->createElement( 'lang' ) );
				$langNode->setAttribute( 'name', $lang );
			}
		}
	}

	public function getLocaleObj( $locale = null ) {

		if( is_null( $locale ) ) {
			$locale = $this->locale;
		}
		if( is_null( $this->locales[$locale] ) ) {
			$this->locales[$locale] = new Locales( $locale );
		}
		return $this->locales[$locale];
	}

	public function configureLocales() {

		if( !isset( $this->siteConfig['locales'] ) ) {
			$this->locale = 'ru_RU';
			$this->locales = array( 'ru_RU' => null );
		} elseif( !is_array( $this->siteConfig['locales'] ) ) {
			$this->locale = $this->siteConfig['locales'];
			$this->locales = array( $this->siteConfig['locales'] => null );
		} else {
			$this->locale = $this->siteConfig['locales'][0];
			foreach( $this->siteConfig['locales'] as $loc ) {
				$this->locales[$loc] = null;
			}
		}
	}

	public function getData( $key ) {

		if( !isset( $this->siteConfig[$key] ) ) {
			return null;
		}
		return $this->siteConfig[$key];
	}
	
	public function getHostname() {

		if( !empty( $_SERVER['HTTP_HOST'] ) ) {
			return $_SERVER['HTTP_HOST'];
		} else {
			return null;
		}
	}

	public function getMainhost() {

		return isset( $_SERVER['VHOST_MAIN'] ) ? $_SERVER['VHOST_MAIN'] : $this->getHostname();
	}

	public function getHost() {

		return $this->host;
	}
	
	public function getBuild() {
	
		static $_build = null;
		if( !is_null( $_build ) ) {
			return $_build;
		}
		
		// try svn versions
		$svnVer = array();
		if( is_file( DIR_SITE . '.svn/entries' ) ) {
			$svn = file( DIR_SITE . '.svn/entries' );
			$svnVer[] = trim( $svn[3] );
		}
		if( is_file( DIR_FW . '.svn/entries' ) ) {
			$svn = file( DIR_FW . '.svn/entries' );
			$svnVer[] = trim( $svn[3] );
		}
		$plugVer = 0;
		foreach( Plugger::getInstance()->plugins as $name=>$val ) {
			if( is_file( DIR_PLUGINS . "$name/.svn/entries" ) ) {
				$svn = file( DIR_PLUGINS . "$name/.svn/entries" );
				$plugVer += trim( $svn[3] );
			}
		}
		if( $plugVer ) {
			$svnVer[] = $plugVer;
		}
		if( !empty( $svnVer ) ) {
			return $_build = implode( '.', $svnVer );
		}

		// at least something
		$ver = '$Rev$';
		if( preg_match( '/\d+/', $ver, $match ) ) {
			return $_build = $match[0];
		}

		return $_build = '-';
	}
}
