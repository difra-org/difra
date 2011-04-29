<?php

include_once 'mysql.php';

/**
 * Реализация мультисайтовой точки входа
 * 
 */
final class Site {

	const PATH_PART = '/../../sites/';

	// libs
	private $locales = array();

	public $project = null;
	private $siteDir = null;
	public $devMode = false;
	private $siteConfig = array();
	private $startTime;
	private $host = null;

	private $phpVersion = null;
	private $version = 'unknown';
	private $pluginsVersion = 'unknown';
	public $bigVersion = 'unknown';

	static public function getInstance( $reset = false ) {

		static $self = null;
		return ( $self and !$reset ) ? $self : $self = new self( );
	}

	public function __construct() {

		if( !$this->detectHost() ) {
			die( 'Invalid server configuration or unconfigured host.' );
		}
		$this->startTime = microtime( true );
		if( is_file( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' ) ) {
			$this->siteConfig = include ( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' );
		}
		$this->configureVersions();
		$this->configurePHP();
		$this->configurePaths();
		$this->configureLocales();
	}

	public function getStats() {
		
		if( !$this->devMode ) {
			return false;
		}
		$time = microtime( true ) - $this->startTime;
		echo "<!--\n";
		echo "Framework version {$this->version}, plugins version {$this->pluginsVersion}\n";
		echo "Page rendered in $time seconds\n";
		echo "Detected cache: " . Cache::getInstance()->adapter . "\n";
		$db = MySQL::getInstance();
		$reqs = $db->queries;
		echo "Made $reqs mysql queries:\n";
		foreach( $db->queriesList as $q ) {
			echo "MySQL: $q\n";
		}
		echo "-->";
	}

	private function detectHost() {

		$sitesDir = dirname( __FILE__ ) . self::PATH_PART;
		if( !empty( $_SERVER['VHOST_NAME'] ) ) {
			$this->host = $_SERVER['VHOST_NAME'];
		} elseif( !empty( $_SERVER['HTTP_HOST'] ) ) {
			$this->host = $_SERVER['HTTP_HOST'];
		} else {
			$this->host = 'default';
		}

		$host = $this->host;
		while( $host ) {
			if( is_dir( $sitesDir . $host ) or is_dir( $sitesDir . 'www.' . $host ) ) {
				$this->host = $host;
				break;
			}
			$host = explode( '.', $host, 2 );
			$host = !empty( $host[1] ) ? $host[1] : false;
		}
		if( !$this->host ) {
			return false;
		}
		if( !$this->siteDir ) {
			if( is_dir( $sitesDir . $this->host ) ) {
				$this->siteDir = $this->host;
			} elseif( is_dir( $sitesDir . 'www.' . $this->host ) ) {
				$this->siteDir = 'www.' . $this->host;
			} elseif( is_dir( $sitesDir . 'default' ) ) {
				$this->siteDir = 'default';
			} else {
				return false;
			}
		}

		if( isset( $_SERVER['VHOST_DEVMODE'] ) and strtolower( $_SERVER['VHOST_DEVMODE'] ) == 'on' ) {
			$this->devMode = true;
		}
		return true;
	}

	private function configureVersions() {

		// Detect framework version

		// Detect version for developers: get it from svn files.
		if( is_readable( dirname( __FILE__ ) . '/.svn/entries' ) ) {
			$svn = file( dirname( __FILE__ ) . '/.svn/entries' );
			$this->version = trim( $svn[3] );
		// Detect version for production: get it from Revision prop.
		} else {
			// TODO: временно обернул в if, надо разобраться с этим
			if( is_file( dirname( __FILE__ ) . '/../../revision.php' ) ) {
				$revisionStr = include( dirname( __FILE__ ) . '/../../revision.php' );
				if( preg_match( '/: ([0-9]+) \$/', $revisionStr, $revisionArr ) ) {
					$this->version = $revisionArr[1];
				}
			}
		}

		// Detect site revision

		// Detect version for developers: get it from svn files.
		if( is_readable( dirname( __FILE__ ) . '/../.svn/entries' ) ) {
			$svn = file( dirname( __FILE__ ) . '/../.svn/entries' );
			$this->pluginsVersion = trim( $svn[3] );
		// Detect version for production: get it from revision.php
		//} else {
		//	$revisionStr = include( dirname( __FILE__ ) . '/../../difra-plugins/revision.php' );
		//	if( preg_match( '/: ([0-9]+) \$/', $revisionStr, $revisionArr ) ) {
		//		$this->pluginsVersion = $revisionArr[1];
		//	}
		//	 
		}

		$this->bigVersion = $this->version . '-' . $this->pluginsVersion;
		if( $this->devMode ) {
			$this->bigVersion .= '-' . microtime( true );
		}
	}

	private function configurePaths() {

		$_SERVER['DOCUMENT_ROOT'] = realpath( dirname( __FILE__ ) . self::PATH_PART . '..' ) . '/';
		define( 'DIR_ROOT', $_SERVER['DOCUMENT_ROOT'] );
		define( 'DIR_SITE', $_SERVER['DOCUMENT_ROOT'] . 'sites/' . $this->siteDir . '/' );
		define( 'DIR_HTDOCS', DIR_SITE . 'htdocs/' );
	}

	private function configurePHP() {

		// get php version
		$this->phpVersion = explode( '.', phpversion() );
		$this->phpVersion = $this->phpVersion[0] * 100 + $this->phpVersion[1];

		// debugging
		ini_set( 'display_errors', $this->devMode ? 'On' : 'Off' );
		if( $this->devMode ) {
			ini_set( 'error_reporting', E_ALL | E_STRICT );
			ini_set( 'html_errors', !empty( $_SERVER['REQUEST_METHOD'] ) ? 'On' : 'Off' );
		}

		// other
		setlocale( LC_ALL, 'UTF8' );
		ini_set( 'short_open_tag', false );
		ini_set( 'asp_tags', false );

		// prepare data
		$this->_stripSlashes();
	}

	private function _stripSlashes() {

		if( get_magic_quotes_gpc() !== 1 ) {
			return false;
		}
		if( $this->phpVersion >= 503 ) {
			$_GET = json_decode( stripslashes( json_encode( $_GET, JSON_HEX_APOS ) ), true );
			$_POST = json_decode( stripslashes( json_encode( $_POST, JSON_HEX_APOS ) ), true );
			$_COOKIE = json_decode( stripslashes( json_encode( $_COOKIE, JSON_HEX_APOS ) ), true );
			$_REQUEST = json_decode( stripslashes( json_encode( $_REQUEST, JSON_HEX_APOS ) ), true );
		} else {
			$_GET = $this->_stripslashesRecursive( $_GET );
			$_POST = $this->_stripslashesRecursive( $_POST );
			$_COOKIE = $this->_stripslashesRecursive( $_COOKIE );
			$_REQUEST = $this->_stripslashesRecursive( $_REQUEST );
		}
	}

	private function _stripslashesRecursive( &$value ) {

		if( is_array( $value ) ) {
			foreach( $value as $k=>$v ) {
				$value[$k] = $this->_stripSlashesRecursive( $v );
			}
		} else {
			$value = stripslashes( $value );
		}
		return $value;
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

	public function getHost() {

		if( $this->host ) {
			return $this->host;
		}
		if( !empty( $this->siteConfig['host'] ) ) {
			return $this->siteConfig['host'];
		}
		return $_SERVER['HTTP_HOST'];
	}
}