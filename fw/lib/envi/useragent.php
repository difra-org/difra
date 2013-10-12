<?php

namespace Difra\Envi;

use Difra\Libs\XML\DOM;

/**
 * Class UserAgent
 *
 * @package Difra\Envi
 */
class UserAgent {

	static $agents = array(
		'OPR' => 'Opera',
		'Chrome' => 'Chrome',
		'CriOS' => 'Chrome',
		'Firefox' => 'Firefox',
		'Opera' => 'Opera',
		'Safari' => 'Safari'
	);

	static $engines = array(
		'AppleWebKit' => 'WebKit',
		'Gecko' => 'Gecko',
		'Presto' => 'Presto',
		'Trident' => 'Trident'
	);

	static $oses = array(
		'Windows' => 'Windows',
		'Macintosh' => 'Macintosh',
		'iPad' => 'iOS',
		'iPod' => 'iOS',
		'iPhone' => 'iOS',
		'Android' => 'Android',
		'BlackBerry' => 'BlackBerry',
		'MeeGo' => 'MeeGo',
		'Linux' => 'Linux'
	);

	/**
	 * Дополняет XML-ноду аттрибутами с информацией о версии браузера пользователя
	 *
	 * @param \DOMElement $node
	 */
	public static function getUserAgentXML( $node ) {

		if( $ua = self::getUserAgent() ) {
			DOM::array2domAttr( $node, $ua );
		}
		if( $uac = self::getUserAgentClass() ) {
			$node->setAttribute( 'uaClass', $uac );
		}
	}

	private static $userAgent = null;

	/**
	 * Возвращает массив с данными о браузере пользователя
	 * @return array
	 */
	public static function getUserAgent() {

		if( !is_null( self::$userAgent ) ) {
			return self::$userAgent;
		}
		return self::$userAgent = array(
			'agent' => self::getAgent(),
			'version' => self::getVersion(),
			'os' => self::getOS(),
			'engine' => self::getEngine(),
			'device' => self::getDevice()
		);
	}

	private static $agentId = null;

	/**
	 * Возвращает id браузера, как он называет себя
	 *
	 * @return string|bool
	 */
	public static function getAgentId() {

		if( !is_null( self::$agentId ) ) {
			return self::$agentId;
		}
		$ua = self::getUAArray();
		foreach( self::$agents as $agent => $aName ) {
			if( isset( $ua[$agent] ) ) {
				return self::$agentId = $agent;
			}
		}
		return self::$agentId = false;
	}

	private static $agent = null;

	/**
	 * Возвращает имя браузера
	 * @return string|bool
	 */
	public static function getAgent() {

		if( !is_null( self::$agent ) ) {
			return self::$agent;
		}
		$agentId = self::getAgentId();
		$ua = self::getUAArray();
		$os = self::getOS();
		if( $os == 'Android' and $agentId == 'Safari' and strpos( $ua['Version'], 'Mobile' ) !== false ) {
			return self::$agent = 'Android-Browser';
		}
		if( $os == 'BlackBerry' and $agentId == 'Safari' and strpos( $ua['Version'], 'Mobile' ) !== false ) {
			return self::$agent = 'BlackBerry-Browser';
		}
		if( $agentId and isset( self::$agents[$agentId] ) ) {
			return self::$agent = self::$agents[$agentId];
		}
		if( isset( $ua['Mozilla'] ) and strpos( $ua['Mozilla'], 'MSIE' ) ) {
			return self::$agentId = 'IE';
		}
		return self::$agent = false;
	}

	private static $engine = null;

	/**
	 * Возвращает движок браузера
	 * @return string|bool
	 */
	public static function getEngine() {

		if( !is_null( self::$engine ) ) {
			return self::$engine;
		}
		$ua = self::getUAArray();
		foreach( self::$engines as $engine => $eName ) {
			if( isset( $ua[$engine] ) ) {
				return self::$engine = $eName;
			}
		}
		return self::$engine = false;
	}

	private static $os = null;
	private static $rawOS = null;

	/**
	 * Возвращает операционную систему пользователя
	 * @return string|bool
	 */
	public static function getOS() {

		if( !is_null( self::$os ) ) {
			return self::$os;
		}
		$uaString = self::getUAString();
		foreach( self::$oses as $os => $osName ) {
			if( strpos( $uaString, $os ) ) {
				self::$rawOS = $os;
				return self::$os = $osName;
			}
		}
		return self::$os = false;
	}

	private static $version = null;

	/**
	 * Возвращает версию браузера
	 * @return string|bool
	 */
	public static function getVersion() {

		if( !is_null( self::$version ) ) {
			return self::$version;
		}
		$ua = self::getUAArray();
		$agent = self::getAgent();
		$agentId = self::getAgentId();
		if( isset( $ua['Version'] ) ) {
			self::$version = $ua['Version'];
			if( substr( self::$version, -7 ) == ' Mobile' ) {
				self::$version = substr( self::$version, 0, -7 );
			}
		} elseif( isset( $ua[$agentId] ) ) {
			if( $agentId == 'Opera' ) {
				self::$version = explode( ' ', $ua[$agentId], 2 )[0];
			} else {
				self::$version = $ua[$agentId];
			}
		} elseif( $agent == 'IE' ) {
			$version = substr( $ua['Mozilla'], strpos( $ua['Mozilla'], 'MSIE' ) + 4 );
			if( $p = strpos( $version, ';' ) ) {
				$version = substr( $version, 0, $p );
			}
			self::$version = trim( $version );
		}
		if( sizeof( $vv = explode( '.', self::$version, 3 ) ) >= 2 ) {
			self::$version = $vv[0] . '.' . $vv[1];
		}
		return self::$version;
	}

	private static $uaString = null;

	/**
	 * Возвращает строку User-Agent
	 *
	 * @return string|bool
	 */
	public static function getUAString() {

		if( !is_null( self::$uaString ) ) {
			return self::$uaString;
		}
		if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return self::$uaString = $_SERVER['HTTP_USER_AGENT'];
		}
		return self::$uaString = false;
	}

	private static $uaArray = null;

	/**
	 * Возвращает ассоциативный массив, сформированный из строки User-Agent
	 *
	 * @return array
	 */
	private static function getUAArray() {

		if( !is_null( self::$uaArray ) ) {
			return self::$uaArray;
		}
		$ua = array();
		preg_match_all( '/([^\/]+)\/([^\/]+)(\s|$)/', self::getUAString(), $ua1 );
		foreach( $ua1[1] as $k => $v ) {
			$ua[$ua1[1][$k]] = $ua1[2][$k];
		}
		return self::$uaArray = $ua;
	}

	private static $uaClass = null;

	/**
	 * Возвращает строку для CSS-классов, основанных на версии браузера
	 *
	 * @return string
	 */
	public static function getUserAgentClass() {

		if( !is_null( self::$uaClass ) ) {
			return self::$uaClass;
		}
		$a = self::getUserAgent();
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
		return self::$uaClass = trim( implode( ' ', $uac ) );
	}

	private static $device = null;

	public static function getDevice() {

		if( !is_null( self::$device ) ) {
			return self::$device;
		}
		$os = self::getOS();
		if( in_array( self::$rawOS, array( 'iPhone', 'iPad', 'iPod' ) ) ) {
			return self::$device = self::$rawOS;
		}
		return self::$device;
	}

	public static function setUAString( $string ) {

		self::$uaString = $string;
		self::$userAgent = null;
		self::$agent = null;
		self::$agentId = null;
		self::$version = null;
		self::$engine = null;
		self::$os = null;
		self::$uaArray = null;
		self::$uaClass = null;
		self::$device = null;
	}
}