<?php

namespace Difra;

use Difra\Envi\Setup;

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
	public static function init() {

		static $initDone = false;
		self::$siteInit = true;
		if( $initDone ) {
			return;
		}
		self::sessionLoad();
		View::addExpires( 0 );
		$initDone = true;
	}

	public static function initDone() {

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
			'locale' => Setup::getLocale(),
			'host' => Envi::getSiteDir(),
			'hostname' => Envi::getHost(),
			'mainhost' => Envi::getHost( true )
		);
	}

	public static function sessionLoad() {

		if( !isset( $_SESSION ) and isset( $_COOKIE[ini_get( 'session.name' )] ) ) {
			session_start();
			if( !isset( $_SESSION['dhost'] ) or $_SESSION['dhost'] != Envi::getHost( true ) ) {
				$_SESSION = array();
			}
		}
	}

	public function sessionStart() {

		$this->sessionLoad();
		if( !isset( $_SESSION ) ) {
			session_start();
			$_SESSION = array();
			$_SESSION['dhost'] = Envi::getHost( true );
		}
	}

	public function sessionSave() {

		if( !empty( $_SESSION ) and empty( $_SESSION['dhost'] ) ) {
			$_SESSION['dhost'] = Envi::getHost( true );
		}
	}
}
