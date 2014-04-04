<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra;

/**
 * Class View
 *
 * @package Difra
 */
class View {

	/** @var bool Page rendered status */
	public static $rendered = false;
	/** @var string XSLT Resourcer instance */
	public static $instance = 'main';

	/**
	 * @param \DOMDocument $xml
	 * @param bool|string  $specificInstance
	 * @param bool         $dontEcho
	 * @param bool         $dontFillXML
	 *
	 * @throws Exception
	 * @internal param bool|string $instance
	 * @return bool|string
	 */
	public static function render( &$xml, $specificInstance = false, $dontEcho = false, $dontFillXML = false ) {

		if( $specificInstance ) {
			$instance = $specificInstance;
		} elseif( self::$instance ) {
			$instance = self::$instance;
		} else {
			$instance = 'main';
		}
		Debugger::addLine( "Render start (instance '$instance')" );

		if( !$resource = Resourcer::getInstance( 'xslt' )->compile( $instance ) ) {
			throw new Exception( "XSLT resource not found" );
		}

		$xslDom = new \DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( $resource ) ) {
			throw new Exception( "XSLT load problem for instance '$instance'" );
		}

		$xslProc = new \XsltProcessor();
		$xslProc->importStyleSheet( $xslDom );

		if( !$dontFillXML and !\Difra\View\Exception::$error and !Debugger::$shutdown ) {
			Controller::getInstance()->fillXML( $xml, $instance );
		}

		// transform template
		if( $html = $xslProc->transformToDoc( $xml ) ) {

			$html = self::normalize( $html );

			if( $dontEcho ) {
				return $html;
			}

			echo $html;
			self::$rendered = true;
			if( Debugger::isEnabled() ) {
				echo '<!-- Page rendered in ' . Debugger::getTimer() . ' seconds -->';
			}
			if( function_exists( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			}
		} else {
			$errormsg = libxml_get_errors(); //error_get_last();
			throw new Exception( $errormsg ? $errormsg['message'] : "Can't render templates" );
		}
		return true;
	}

	/**
	 * Редирект
	 *
	 * @param $url
	 */
	public static function redirect( $url ) {

		self::$rendered = true;
		header( 'Location: ' . $url );
		die();
	}

	/**
	 * Добавляет заголовки Expires и X-Accel-Expires
	 *
	 * @param $ttl
	 */
	public static function addExpires( $ttl ) {

		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', $ttl ? ( time() + $ttl ) : 0 ) );
		if( isset( $SERVER['SERVER_SOFTWARE'] ) and substr( $_SERVER['SERVER_SOFTWARE'], 0, 5 ) == 'nginx' ) {
			header( 'X-Accel-Expires: ' . ( $ttl ? $ttl : 'off' ) );
		}
	}

	public static function normalize( $htmlDoc ) {

		$normalizerXml = View\Normalizer::getXML();
		$normalizerDoc = new \DOMDocument();
		$normalizerDoc->loadXML( $normalizerXml );
		$normalizerProc = new \XSLTProcessor();
		$normalizerProc->importStylesheet( $normalizerDoc );
		return $normalizerProc->transformToXML( $htmlDoc );
	}
}
