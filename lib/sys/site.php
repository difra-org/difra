<?php

include_once 'mysql.php';

/**
 * Реализация мультисайтовой точки входа
 * 
 */
final class Site {

	const PATH_PART = '/../../../../sites/';
	private $project = null;
	private $siteDir = null;
	public $devMode = false;
	private $siteConfig = null;
	private $startTime;
	private $host = null;

	/**
	 * Singleton
	 *
	 * @return Sys_Site
	 */
	static public function getInstance( $reset = false ) {

		static $self = null;
		return ( $self and !$reset ) ? $self : $self = new self( );
	}

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		if( !$this->detectHost() ) {
			die( 'Invalid server configuration or unconfigured host.' );
		}
		$this->startTime = microtime( true );
		$this->siteConfig = include ( dirname( __FILE__ ) . self::PATH_PART . $this->siteDir . '/config.php' );
		$this->configurePHP();
		$this->configurePaths();
	}
	
	public function getStats() {
		
		if( $this->devMode ) {
			$time = microtime( true ) - $this->startTime;
			$db = MySQL::getInstance();
			$reqs = $db->queries;
			echo "<!-- Page rendered in $time seconds, made $reqs mysql queries -->\n";
			foreach( $db->queriesList as $q ) {
				echo "<!-- MySQL: $q -->\n";
			}
		}
	}

	/**
	 * Определяет хост:
	 * 1. Смотрим серверные переменные VHOST_NAME и VHOST_DEVMODE
	 * 2. Если нет VHOST_NAME, пытаемся определить домен по проекту
	 *
	 */
	private function detectHost() {

		$sitesDir = dirname( __FILE__ ) . self::PATH_PART;
		$this->host = $_SERVER['HTTP_HOST'] or $_SERVER['VHOST_NAME'];

		if( !empty( $_SERVER['VHOST_NAME'] ) ) {
			$this->project = $_SERVER['VHOST_NAME'];
		} else {
			$host = $_SERVER['HTTP_HOST'];
			while( $host ) {
				if( is_dir( $sitesDir . $host ) or is_dir( $sitesDir . 'www.' . $host ) ) {
					$this->project = $this->host = $host;
					break;
				}
				$host = explode( '.', $host, 2 );
				$host = !empty( $host[1] ) ? $host[1] : false;
			}
		}
		if( !$this->project ) {
			return false;
		}
		if( !$this->siteDir ) {
			if( is_dir( $sitesDir . $host ) ) {
				$this->siteDir = $this->project;
			} elseif( is_dir( $sitesDir . 'www.' . $host ) ) {
				$this->siteDir = 'www.' . $this->project;
			} else {
				return false;
			}
		}

		if( isset( $_SERVER['VHOST_DEVMODE'] ) and strtolower( $_SERVER['VHOST_DEVMODE'] ) == 'on' ) {
			$this->devMode = true;
		}
		return true;
	}

	private function configurePaths() {

		$_SERVER['DOCUMENT_ROOT'] = realpath( dirname( __FILE__ ) . self::PATH_PART . '..' ) . '/';
		define( 'DIR_ROOT', $_SERVER['DOCUMENT_ROOT'] );
		define( 'DIR_SITE', $_SERVER['DOCUMENT_ROOT'] . 'sites/' . $this->siteDir . '/' );
		define( 'DIR_HTDOCS', DIR_SITE . 'htdocs/' );
	}

	/**
	 * Changes PHP configuration variables to run smooth on various systems
	 */
	private function configurePHP() {

		ini_set( 'display_errors', $this->devMode ? 1 : 0 );
		if( $this->devMode ) {
			ini_set( 'error_reporting', E_ALL | E_STRICT );
		}
		if( get_magic_quotes_gpc() === 1 ) {
			$_GET = $this->_stripslashesRecursive( $_GET );
			$_POST = $this->_stripslashesRecursive( $_POST );
			$_COOKIE = $this->_stripslashesRecursive( $_COOKIE );
			$_REQUEST = $this->_stripslashesRecursive( $_REQUEST );
			/* XXX: in php 5.3+ this may be used:
			$_REQUEST = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS)), true);
			 */
		}
	}
	function _stripslashesRecursive( &$value ) {

		if( is_array( $value ) ) {
			foreach( $value as $k=>$v ) {
				$value[$k] = $this->_stripSlashesRecursive( $v );
			}
		} else {
			$value = stripslashes( $value );
		}
		return $value;
	}

	/**
	 * Возвращает массив с параметрами подключения к базе
	 *
	 * @return array
	 */
	public function getDbConfig() {

		return $this->siteConfig['db'];
	}

	/**
	 * Возвращает строку локали (ru_RU, en_US и т.д.)
	 *
	 * @return string
	 */
	public function getLocale() {

		return isset( $this->siteConfig['locale'] ) ? $this->siteConfig['locale'] : 'ru_RU';
	}

	/**
	 * Возвращает данные по ключу
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getData( $key ) {

		if( !isset( $this->siteConfig['data'][$key] ) ) {
			error( 'No data with key: ' . $key, __FILE__, __LINE__ );
			return null;
		}
		return $this->siteConfig['data'][$key];
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
