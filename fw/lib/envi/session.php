<?php

namespace Difra\Envi;

use Difra\Envi;

/**
 * Class Session
 *
 * @package Difra\Envi
 */
class Session {

	/**
	 * Constructor: load session.
	 */
	public function __construct() {

		self::load();
	}

	/**
	 * Destructor: save session.
	 */
	public function __destruct() {

		$this->save();
	}

	/**
	 * Session init.
	 * Must be called at least once on init phase.
	 */
	public static function init() {

		static $instance = null;
		if( is_null( $instance ) ) {
			$instance = new self;
		}
	}

	/**
	 * Load session
	 */
	private static function load() {

		if( !isset( $_SESSION ) and isset( $_COOKIE[ini_get( 'session.name' )] ) ) {
			session_start();
			if( !isset( $_SESSION['dhost'] ) or $_SESSION['dhost'] != Envi::getHost( true ) ) {
				$_SESSION = array();
			}
		}
	}

	/** Start session */
	public static function start() {

		self::load();
		if( !isset( $_SESSION ) ) {
			session_start();
			$_SESSION = array();
			$_SESSION['dhost'] = Envi::getHost( true );
		}
	}

	/** Save session */
	private static function save() {

		if( !empty( $_SESSION ) and empty( $_SESSION['dhost'] ) ) {
			$_SESSION['dhost'] = Envi::getHost( true );
		}
	}
}