<?php

namespace Difra\Envi;

use Difra\Envi;

class Setup {

	public static function run() {

		// paths
		if( !defined( 'DIR_ROOT' ) ) {
			define( 'DIR_ROOT', __DIR__ . '/../../../' );
		}
		$_SERVER['DOCUMENT_ROOT'] = DIR_ROOT;
		define( 'DIR_FW', ( defined( 'DIR_PHAR' ) ? DIR_PHAR : DIR_ROOT ) . 'fw/' );
		define( 'DIR_SITE', DIR_ROOT . 'sites/' . Envi::getSiteDir() . '/' );
		define( 'DIR_PLUGINS', ( defined( 'DIR_PHAR' ) ? DIR_PHAR : DIR_ROOT ) . 'plugins/' );
		define( 'DIR_DATA', !empty( $_SERVER['VHOST_DATA'] ) ? $_SERVER['VHOST_DATA'] . '/' : DIR_ROOT . 'data/' );

		// other
		mb_internal_encoding( 'UTF-8' );
		ini_set( 'short_open_tag', false );
		ini_set( 'asp_tags', false );
		ini_set( 'mysql.trace_mode', false );

		// set session domain
		ini_set( 'session.use_cookies', true );
		ini_set( 'session.use_only_cookies', true );
		ini_set( 'session.cookie_domain', '.' . \Difra\Envi::getHost( true ) );

		// set default time zone
		if( !ini_get( 'date.timezone' ) ) {
			date_default_timezone_set( 'Europe/Moscow' );
		}

		// prepare data
		if( get_magic_quotes_gpc() ) {
			$strip_slashes_deep = function ( $value ) use ( &$strip_slashes_deep ) {

				return is_array( $value ) ? array_map( $strip_slashes_deep, $value ) : stripslashes( $value );
			};
			$_GET = array_map( $strip_slashes_deep, $_GET );
			$_POST = array_map( $strip_slashes_deep, $_POST );
			$_COOKIE = array_map( $strip_slashes_deep, $_COOKIE );
		}
	}

	static private $locale = 'ru_RU';

	public static function setLocale( $locale ) {

		self::$locale = $locale;
		setlocale( LC_ALL, array( self::$locale . '.UTF-8', self::$locale . '.utf8' ) );
		setlocale( LC_NUMERIC, array( 'en_US.UTF-8', 'en_US.utf8' ) );
	}

	public static function getLocale() {

		return self::$locale;
	}
}