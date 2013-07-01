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
			'engine' => self::getEngine()
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
		if( $agentId = self::getAgentId() and isset( self::$agents[$agentId] ) ) {
			return self::$agent = self::$agents[$agentId];
		}
		$ua = self::getUAArray();
		if( isset( $ua['Mozilla'] ) and strpos( $ua['Mozilla'], 'MSIE' ) ) {
			return self::$agentId = 'IE';
		}
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
		if( isset( $ua['Version'] ) ) {
			return self::$version = $ua['Version'];
		}
		$agentId = self::getAgentId();
		if( isset( $ua[$agentId] ) ) {
			if( $agentId == 'Opera' ) {
				return self::$version = explode( ' ', $ua[$agentId], 2 )[0];
			} else {
				return self::$version = $ua[$agentId];
			}
		}
		$agent = self::getAgent();
		if( $agent == 'IE' ) {
			$version = substr( $ua['Mozilla'], strpos( $ua['Mozilla'], 'MSIE' ) + 4 );
			if( $p = strpos( $version, ';' ) ) {
				$version = substr( $version, 0, $p );
			}
			return self::$version = trim( $version );
		}
		return self::$version = false;
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
	}
}