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
		$xslDom = new DomDocument;
		if( !$xslDom->load( $xslFile ) ) {
			error( "Can't load template $xslFile", __FILE__, __LINE__ );
			if( !$dontEcho and !$errorPage ) {
				$this->httpError( 500 );
			} else {
				return false;
			}
		}
		$this->_getExtTemplates( $xslDom, $template );
		$xslProc = new XsltProcessor();
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

	private function _getExtTemplates( &$xml, $template = 'main.xsl' ) {

		$data = Plugger::getInstance()->getTemplates( $template );
		if( !$data ) {
			return false;
		}
		foreach( $data as $template ) {
			$elem = $xml->createElementNS( 'http://www.w3.org/1999/XSL/Transform', 'xsl:include' );
			$etNode = $xml->documentElement->appendChild( $elem );
			$etNode->setAttribute( 'href', $template );
		}
	}

	private function _getTemplatePath( $template ) {

		if( file_exists( DIR_SITE . "templates/$template" ) ) {
			return DIR_SITE . "templates/$template";
		}
		$paths = Plugger::getInstance()->getTemplates( $template );
		return empty( $paths ) ? false : $paths[0];
	}
}
