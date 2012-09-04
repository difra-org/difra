<?php

namespace Difra;

class Site {

	const VERSION   = '2.1';
	const BUILD     = '$Rev$';
	const PATH_PART = '/../../sites/';

	private $locale = 'ru_RU';

	private $siteDir = null;
	private $host = null;

	private $phpVersion = null;

	private $userAgent = null;
	static $siteInit = false;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		Events::register( 'core-init', 'Difra\\Site', 'init' );
		Events::register( 'core-init', 'Difra\\Debugger' );
		Events::register( 'plugins-load', 'Difra\Plugger', 'init' );
		Events::register( 'init-done', 'Difra\\Site', 'initDone' );
		Events::register( 'action-find', 'Difra\\Action', 'find' );
		Events::register( 'action-run', 'Difra\\Action', 'run' );
		Events::register( 'render-run', 'Difra\\Action', 'render' );
	}

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
		$initDone = true;
	}

	public function initDone() {

		self::$siteInit = false;
	}

	public static function isInit() {

		return self::$siteInit;
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

		$this->siteDir = $this->host;
		if( !is_dir( $sitesDir . $this->host ) and is_dir( $sitesDir . 'www.' . $this->host ) ) {
			$this->siteDir = 'www.' . $this->host;
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
		define( 'DIR_DATA', realpath( isset( $_SERVER['VHOST_DATA'] ) ? $_SERVER['VHOST_DATA'] : DIR_SITE . 'data/' ) . '/' );
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
		$this->_stripSlashes();
	}

	/**
	 * Убирает слеши из $_GET, $_POST, $_COOKIE, $_REQUEST, если включены magic quotes
	 *
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

	public function getLocale() {

		return $this->locale;
	}

	public function configureLocale() {

		if( $locale = Config::getInstance()->get( 'locale' ) ) {
			$this->locale = $locale;
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

	/**
	 * Получить версию ревизии SVN
	 *
	 * @param string $dir Путь к папке со слэшем в конце
	 *
	 * @return int|bool
	 */
	private function getSVNRev( $dir ) {

		// try to get svn 1.7 revision
		if( class_exists( '\SQLite3' ) and is_readable( $dir . '.svn/wc.db' ) ) {
			$sqlite = new \SQLite3( $dir . '.svn/wc.db' );
			$res    = $sqlite->query( 'SELECT MAX(revision) FROM `NODES`' );
			$res    = $res->fetchArray();
			return $res[0];
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

	public function getBuild( $asArray = false ) {

		static $_build = null;
		static $_array = null;

		if( !$asArray and !is_null( $_build ) ) {
			return $_build;
		} elseif( $asArray and !is_null( $_array ) ) {
			return $_array;
		}

		$svnVer = array();
		// fw version and build
		$fwVer = $this->getSVNRev( DIR_FW );
		if( $fwVer !== false ) {
			$svnVer[] = self::VERSION . '.' . $fwVer;
		} elseif( preg_match( '/\d+/', self::BUILD, $match ) ) {
			$svnVer[] = self::VERSION . '.' . $match[0];
		} else {
			$svnVer[] = self::VERSION;
		}
		// plugins build summ
		$pluginPaths = Plugger::getInstance()->getPaths();
		$plugVer     = 0;
		foreach( $pluginPaths as $path ) {
			$plugVer += $this->getSVNRev( $path );
		}
		if( $plugVer ) {
			$svnVer[] = $plugVer;
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
	 * @param \DOMNode $node
	 */
	public function getConfigXML( $node ) {

		$node->setAttribute( 'locale', $this->locale );
		$node->setAttribute( 'host', $this->getHost() );
		$node->setAttribute( 'hostname', $this->getHostname() );
		$node->setAttribute( 'mainhost', $this->getMainhost() );
	}

	public function getData( $key ) {

		trigger_error( 'Site->getData() is deprecated, please use Config->get', E_USER_NOTICE );
		return Config::getInstance()->get( $key );
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
			$_SESSION          = array();
			$_SESSION['dhost'] = $this->getMainhost();
		}
	}

	public function sessionSave() {

		if( !empty( $_SESSION ) and empty( $_SESSION['dhost'] ) ) {
			$_SESSION['dhost'] = $this->getMainhost();
		}
	}

	public function getUserAgent( $node ) {

		if( is_null( $this->userAgent ) and !isset( $_SERVER['HTTP_USER_AGENT'] ) or !$_SERVER['HTTP_USER_AGENT'] ) {
			$this->userAgent = false;
		} else {
			// разбиваем строку User-Agent на ассоциативный массив
			$ua  = $_SERVER['HTTP_USER_AGENT'];
			$ua1 = explode( ' ', $ua );
			$k   = '';
			$v   = $ua2 = array();
			foreach( $ua1 as $p ) {
				if( strpos( $p, '/' ) !== false ) {
					if( $k ) {
						$ua2[$k] = implode( ' ', $v );
						$v       = array();
					}
					$p2  = explode( '/', $p, 2 );
					$k   = $p2[0];
					$v[] = $p2[1];
				} else {
					$v[] = $p;
				}
			}
			if( $k ) {
				$ua2[$k] = implode( ' ', $v );
			}
			$a = array(
				'agent'   => '',
				'version' => '',
				'os'      => '',
				'engine'  => ''
			);
			// пытаемся определить название браузера
			if( isset( $ua2['Chrome'] ) ) {
				$a['agent'] = 'Chrome';
			} elseif( isset( $ua2['Safari'] ) ) {
				$a['agent'] = 'Safari';
			} elseif( isset( $ua2['Firefox'] ) ) {
				$a['agent'] = 'Firefox';
			} elseif( isset( $ua2['Opera'] ) ) {
				$a['agent'] = 'Opera';
			} elseif( isset( $ua2['Mozilla'] ) and strpos( $ua2['Mozilla'], 'MSIE' ) ) {
				$a['agent'] = 'IE';
			}
			// пытаемся определить движок
			if( isset( $ua2['AppleWebKit'] ) ) {
				$a['engine'] = 'WebKit';
			} elseif( isset( $ua2['Gecko'] ) ) {
				$a['engine'] = 'Gecko';
			} elseif( isset( $ua2['Presto'] ) ) {
				$a['engine'] = 'Presto';
			} elseif( isset( $ua2['Trident'] ) ) {
				$a['engine'] = 'Trident';
			}
			// пытаемся определить версию
			if( isset( $ua2['Version'] ) ) {
				$a['version'] = $ua2['Version'];
			} elseif( isset( $ua2[$a['agent']] ) ) {
				$a['version'] = $ua2[$a['agent']];
				if( $a['agent'] == 'Opera' ) {
					$a['version'] = explode( ' ', $a['version'] )[0];
				}
			} elseif( $a['agent'] == 'IE' and isset( $ua2['Mozilla'] ) and strpos( $ua2['Mozilla'], 'MSIE' ) ) {
				$a['version'] = substr( $ua2['Mozilla'], strpos( $ua2['Mozilla'], 'MSIE' ) + 4 );
				if( $p = strpos( $a['version'], ';' ) ) {
					$a['version'] = substr( $a['version'], 0, $p );
				}
				$a['version'] = trim( $a['version'] );
			}
			// пытаемся определить ось стандартными методами
			$os1 = array( 'Mozilla' );
			if( $a['agent'] ) {
				$os1[] = $a['agent'];
			}
			$os2 = array( 'Windows', 'Macintosh', 'Linux' );
			foreach( $os1 as $v1 ) {
				if( !isset( $ua2[$v1] ) ) {
					continue;
				}
				foreach( $os2 as $v2 ) {
					if( strpos( $ua2[$v1], $v2 ) ) {
						$a['os'] = $v2;
						break 2;
					}
				}
			}
			if( !$a['os'] ) {
				if( $a['agent'] == 'Opera' and isset( $ua2['Tablet'] ) ) {
					$a['os'] = 'Tablet';
				} elseif( $a['agent'] == 'Opera' and isset( $ua2['Mobi'] ) ) {
					$a['os'] = 'Mobile';
				}
			}
			$this->userAgent = $a;
		}
		if( $this->userAgent == false ) {
			return;
		}
		// fill XML data
		foreach( $this->userAgent as $k => $v ) {
			$node->setAttribute( $k, $v );
		}
		$a = $this->userAgent;
		if( !empty( $a ) ) {
			$uac = array();
			if( $a['agent'] ) {
				$uac[] = $a['agent'];
			}
			if( $a['version'] ) {
				$uac[] = 'v' . intval( $a['version'] );
				$uac[] = 'vv' . str_replace( array( '.', ' ' ), '_', $a['version'] );
			}
			if( $a['os'] ) {
				$uac[] = $a['os'];
			}
			if( $a['engine'] ) {
				$uac[] = $a['engine'];
			}
			$node->setAttribute( 'uaClass', implode( ' ', $uac ) );
		}
	}
}
