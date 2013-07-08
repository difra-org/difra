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
