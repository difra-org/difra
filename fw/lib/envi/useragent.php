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

	static $os = array(
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

		$ua = self::getUserAgent();
		if( $ua ) {
			DOM::array2domAttr( $node, $ua );
		}
		$uac = self::getUserAgentClass();
		if( $uac ) {
			$node->setAttribute( 'uaClass', $uac );
		}
	}

	/**
	 * Возвращает массив с данными о браузере пользователя
	 * @return array|bool
	 */
	public static function getUserAgent() {

		static $userAgent = null;
		if( !is_null( $userAgent ) ) {
			return $userAgent;
		}
		if( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$userAgent = false;
			return $userAgent;
		}
		// разбиваем строку User-Agent на ассоциативный массив
		preg_match_all( '/([^\/]+)\/([^\/]+)(\s|$)/', $ua0 = $_SERVER['HTTP_USER_AGENT'], $ua1 );
		$ua = array();
		foreach( $ua1[1] as $k => $v ) {
			$ua[$ua1[1][$k]] = $ua1[2][$k];
		}
		$a = array(
			'agent' => '',
			'version' => '',
			'os' => '',
			'engine' => ''
		);
		// определяем название браузера
		if( isset( $ua['Mozilla'] ) and strpos( $ua['Mozilla'], 'MSIE' ) ) {
			$a['agent'] = 'IE';
		} else {
			foreach( self::$agents as $agent => $aName ) {
				if( isset( $ua[$agent] ) ) {
					$a['agent'] = $aName;
					$a['agentId'] = $agent;
					break;
				}
			}
		}
		// определяем движок
		foreach( self::$engines as $engine => $eName ) {
			if( isset( $ua[$engine] ) ) {
				$a['engine'] = $eName;
				break;
			}
		}
		// определяем версию
		if( isset( $ua['Version'] ) ) {
			$a['version'] = $ua['Version'];
		} elseif( isset( $ua[$a['agentId']] ) ) {
			$a['version'] = $ua[$a['agentId']];
			if( $a['agent'] == 'Opera' ) {
				$a['version'] = explode( ' ', $a['version'], 2 )[0];
			}
		} elseif( $a['agent'] == 'IE' and isset( $ua['Mozilla'] ) and strpos( $ua['Mozilla'], 'MSIE' ) ) {
			$a['version'] = substr( $ua['Mozilla'], strpos( $ua['Mozilla'], 'MSIE' ) + 4 );
			if( $p = strpos( $a['version'], ';' ) ) {
				$a['version'] = substr( $a['version'], 0, $p );
			}
			$a['version'] = trim( $a['version'] );
		}
		// определяем платформу
		foreach( self::$os as $os => $osName ) {
			if( strpos( $ua0, $os ) ) {
				$a['os'] = $osName;
				break;
			}
		}
		unset( $a['agentId'] );
		return $userAgent = $a;
	}

	/**
	 * Возвращает строку для CSS-классов, основанных на версии браузера
	 *
	 * @return string
	 */
	public static function getUserAgentClass() {

		static $uaClass = null;
		if( !is_null( $uaClass ) ) {
			return $uaClass;
		}
		$a = self::getUserAgent();
		if( empty( $a ) ) {
			return $uaClass = '';
		}
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
		return $uaClass = trim( implode( ' ', $uac ) );
	}
}