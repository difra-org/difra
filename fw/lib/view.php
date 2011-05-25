<?php

class View {

	public $error = false;
	public $redirect = false;
	public $rendered = false;
	public $template = false;
	public $instance = 'main';

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
			if( $this->template ) {
				$template = $this->template;
			} elseif( $this->instance ) {
				$template = $this->instance;
			} else {
				$template = 'main';
			}
		}
		
		$xslDom = new DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( Resourcer::getInstance( 'xslt' )->compile( $template ) ) ) {
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

		// transform template
		if( $html = $xslProc->transformToDoc( $xml ) ) {
			$devMode = Debugger::getInstance()->isEnabled();
			$html->formatOutput = $devMode;
			$html->preserveWhiteSpace = $devMode;
			if( $dontEcho ) {
				return $html->saveXML();
			}
			// эта строка ломает CKEditor, поэтому она накакзана
			//header( 'Content-Type: application/xhtml+xml; charset=UTF-8' );
			echo( $html->saveXML() );
			Debugger::getInstance()->printOutput();
		} else {
			throw new exception( "Can't render templates" );
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
}
