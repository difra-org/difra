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
			error( "Can't find template $xslFile", __FILE__, __LINE__ );
			if( !$dontEcho and !$errorPage ) {
				$this->httpError( 500 );
			} else {
				return false;
			}
		}
		
		// Создание обёртки
		$xslWrapper = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
		if( is_file( DIR_ROOT . 'templates/xhtml-lat1.ent' ) and
		   is_file( DIR_ROOT . 'templates/xhtml-special.ent' ) and
		   is_file( DIR_ROOT . 'templates/xhtml-symbol.ent' ) ) {
			$xslWrapper .= '
				<!DOCTYPE xsl:stylesheet [
				<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'templates/xhtml-lat1.ent">
				<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'templates/xhtml-symbol.ent">
				<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'templates/xhtml-special.ent">
				%lat1;
				%symbol;
				%special;
				]>';
		}
		$xslWrapper .= '
			<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
				<xsl:output method="xml" indent="yes" encoding="utf-8" media-type="text/html"
					doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
					doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
					cdata-section-elements="script style" />';
		$includes = $this->_getExtTemplates( $template );
		$includes[] = $xslFile;
		foreach( $includes as $inc ) {
			$xslWrapper .= '<xsl:include href="' . $inc . '"/>';
		}
		$xslWrapper .= '</xsl:stylesheet>';
		
		$xslDom = new DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( $xslWrapper ) ) {
			error( "XSLT loader problem.", __FILE__, __LINE__ );
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
				echo( $html->saveXML() );
				Site::getInstance()->getStats();
			} else {
				return $html->saveXML();
			}
		} else {
			error( 'Can\'t transform template ' . DIR_SITE . "templates/$template", __FILE__, __LINE__ );
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
		if( is_file( DIR_ROOT . 'templates/all.xsl' ) ) {
			$data[] = DIR_ROOT . 'templates/all.xsl';
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
}
