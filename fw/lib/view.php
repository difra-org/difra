<?php

namespace Difra;

/**
 * Class View
 *
 * @package Difra
 */
class View {

	/**
	 * @var bool
	 * @deprecated
	 */
	public static $error = false;
	/**
	 * @var bool|string
	 * @deprecated
	 */
	public static $redirect = false;
	/** @var bool */
	public static $rendered = false;
	/**
	 * @var bool|string
	 * @deprecated
	 */
	public static $template = false;
	/** @var string */
	public static $instance = 'main';

	/**
	 * @param \DOMDocument $xml
	 * @param bool|string  $specificInstance
	 * @param bool         $dontEcho
	 * @throws exception
	 * @internal param bool|string $instance
	 * @return bool|string
	 */
	public static function render( &$xml, $specificInstance = false, $dontEcho = false ) {

		if( self::$error or self::$redirect ) {
			return false;
		}
		if( $specificInstance ) {
			$instance = $specificInstance;
		} elseif( self::$instance ) {
			$instance = self::$instance;
		} else {
			$instance = 'main';
		}
		Debugger::addLine( "Render start (instance '$instance')" );

		if( !$resource = Resourcer::getInstance( 'xslt' )->compile( $instance ) ) {
			throw new exception( "XSLT resource not found" );
		}

		$xslDom = new \DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( $resource ) ) {
			throw new exception( "XSLT load problem for instance '$instance'" );
		}

		$xslProc = new \XsltProcessor();
		$xslProc->importStyleSheet( $xslDom );

		Controller::getInstance()->fillXML( $instance );

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
			throw new exception( $errormsg ? $errormsg['message'] : "Can't render templates" );
		}
		return true;
	}

	/**
	 * Редирект
	 * @param $url
	 */
	public static function redirect( $url ) {

		self::$redirect = true;
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

		$normalizerXml = include( 'view/normalizer.php' );
		$normalizerDoc = new \DOMDocument();
		$normalizerDoc->loadXML( $normalizerXml );
		$normalizerProc = new \XSLTProcessor();
		$normalizerProc->importStylesheet( $normalizerDoc );
		return $normalizerProc->transformToXML( $htmlDoc );
	}
}
