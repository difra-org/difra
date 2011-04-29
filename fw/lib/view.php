<?php

class View {

	public $error = false;
	public $redirect = false;
	public $rendered = false;
	public $template = false;
	public $menu = 'menu.xml';

	static function getInstance() {

		static $_instance = null;
		if( !$_instance ) {
			$_instance = new self( );
		}
		return $_instance;
	}

	public function __construct() {

	}

	public function httpError( $err ) {

		if( $this->redirect ) {
			return false;
		}
		$errors = include ( 'http_errors.php' );

		if( isset( $errors[$err] ) ) {
			$error = $errors[$err];
		} else {
			$error = 'Unknown';
		}
		header( "HTTP/1.1 $err $error" );
		$renderProblem = false;
		if( file_exists( DIR_SITE . 'templates/errors/' . $err . '.xsl' ) ) {
			$xml = new DOMDocument( );
			$root = $xml->appendChild( $xml->createElement( 'error' . $err ) );
			$root->setAttribute( 'host', Site::getInstance()->getHost() );
			$renderProblem = !$this->render( $xml, 'errors/' . $err . '.xsl', false, true );
			$this->error = $err;
		}
		if( !file_exists( DIR_SITE . 'templates/errors/' . $err . '.xsl' ) or $renderProblem ) {
			$this->error = $err;
			die( '<center><h1 style="padding:350px 0px 0px 0px">HTTP error ' . $err . ' (' . $error . ')</h1></center>' );
		}
	}

	public function render( $xml, $template = false, $dontEcho = false, $errorPage = false ) {

		if( !$dontEcho ) {
			$this->rendered = true;
		}
		if( $this->error or $this->redirect ) {
			return false;
		}
		if( !$template ) {
			$template = $this->template ? $this->template : 'main.xsl';
		}

		$xslFile = $this->_getTemplatePath( $template );
		if( !$xslFile ) {
			throw new exception( "Can't find template $xslFile" );
			if( !$dontEcho and !$errorPage ) {
				$this->httpError( 500 );
			} else {
				return false;
			}
		}
		
		
		// Создание обёртки
		$cacheKey = 'template_' . $template;
		if( !$xslWrapper = Cache::getInstance()->smartGet( $cacheKey ) ) {
			$includes = $this->_getExtTemplates( $template );
			$includes[] = $xslFile;
			$xslInner = '';
			foreach( $includes as $inc ) {
				$xslInner .= "<xsl:include href=\"$inc\"/>";
			}
			$xslWrapper = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>
				<!DOCTYPE xsl:stylesheet [
				<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'fw/templates/xhtml-lat1.ent">
				<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'fw/templates/xhtml-symbol.ent">
				<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'fw/templates/xhtml-special.ent">
				%lat1;
				%symbol;
				%special;
				]>
				<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
				<xsl:output method="xml" indent="yes" encoding="utf-8" omit-xml-declaration="yes" doctype-system="about:legacy-compat"/>'
				. $this->_extendXSL( $xslInner ) . '</xsl:stylesheet>';
			Cache::getInstance()->smartPut( $cacheKey, $xslWrapper );
		}
		
		$xslDom = new DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( $xslWrapper ) ) {
			throw new exception( "XSLT loader problem." );
			if( !$dontEcho and !$errorPage ) {
				$this->httpError( 500 );
			} else {
				return false;
			}
		}
		$xslProc = new XsltProcessor();
		$xslProc->resolveExternals = true;
		$xslProc->substituteEntities = true;
		$xslProc->importStyleSheet( $xslDom );

		// Transform to HTML or whatever template specifies
		if( $html = $xslProc->transformToDoc( $xml ) ) {
			$devMode = Site::getInstance()->devMode;
			$html->formatOutput = $devMode;
			$html->preserveWhiteSpace = $devMode;
			if( !$dontEcho ) {
				//header( 'Content-Type: application/xhtml+xml; charset=UTF-8' );
				echo( $html->saveXML() );
				Site::getInstance()->getStats();
			} else {
				return $html->saveXML();
			}
		} else {
			throw new exception( 'Can\'t transform template ' . DIR_SITE . "templates/$template" );
			if( !$errorPage ) {
				$this->httpError( 500 );
			} else {
				return false;
			}
		}
		return true;
	}

	public function redirect( $url ) {

		$this->redirect = true;
		header( 'Location: ' . $url );
	}

	private function _getExtTemplates( $template = 'main.xsl' ) {

		// шаблоны из плагинов
		$data = Plugger::getInstance()->getTemplates( $template );
		// общие шаблоны из движка
		if( is_file( DIR_ROOT . 'fw/templates/all.xsl' ) ) {
			$data[] = DIR_ROOT . 'fw/templates/all.xsl';
		}
		return $data;
		/*
		foreach( $data as $template ) {
			$elem = $xml->createElementNS( 'http://www.w3.org/1999/XSL/Transform', 'xsl:include' );
			$etNode = $xml->documentElement->appendChild( $elem );
			$etNode->setAttribute( 'href', $template );
		}
		 */
	}

	private function _getTemplatePath( $template ) {

		if( file_exists( DIR_SITE . "templates/$template" ) ) {
			return DIR_SITE . "templates/$template";
		}
		$paths = Plugger::getInstance()->getTemplates( $template );
		return empty( $paths ) ? false : $paths[0];
	}
	
	private function _extendXSL( $text, $path = '/', $depth = 1 ) {

		if( $depth > 10 ) {
			throw new exception( 'Too long XSLT includes recursion depth.' );
		}
		while( true ) {
			preg_match( '/(.*?)<xsl:include href="(.*?)"\/\>(.*)/is', $text, $matches );
			if( empty( $matches ) ) {
				return $text;
			}
			preg_match( '/<xsl\:stylesheet.*?\>(.*)<\/xsl\:stylesheet\>/is',
				   file_get_contents( $matches[2]{0} != '/' ? "$path/{$matches[2]}" : $matches[2] ),
				   $newMatches );
			if( empty( $newMatches ) ) {
				throw new exception( "Can't find <xsl:stylesheet> section in {$matches[2]}" );
			}
			$text = $matches[1] . $this->_extendXSL( $newMatches[1], dirname( $matches[2] ), $depth + 1 ) . $matches[3];
		}
	}
}