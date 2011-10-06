<?php

namespace Difra;

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

		if( $this->redirect or $this->error ) {
			return;
		}
		$errors = include ( 'http_errors.php' );

		if( isset( $errors[$err] ) ) {
			$error = $errors[$err];
		} else {
			$error = 'Unknown';
		}
		header( "HTTP/1.1 $err $error" );
		try {
			$xml = new \DOMDocument( );
			$root = $xml->appendChild( $xml->createElement( 'error' . $err ) );
			$root->setAttribute( 'host', Site::getInstance()->getHost() );
			$this->render( $xml, 'error_' . $err, false, true );
			$this->error = $err;
		} catch( exception $ex ) {
			$this->error = $err;
			die( '<center><h1 style="padding:350px 0px 0px 0px">HTTP error ' . $err . ' (' . $error . ')</h1></center>' );
		}
		die();
	}

	public function render( $xml, $instance = false, $dontEcho = false, $errorPage = false ) {

		if( !$dontEcho ) {
			$this->rendered = true;
		}
		if( $this->error or $this->redirect ) {
			return false;
		}
		if( !$instance ) {
			if( $this->template ) {
				$instance = $this->template;
			} elseif( $this->instance ) {
				$instance = $this->instance;
			} else {
				$instance = 'main';
			}
		}
		
		$xslDom = new \DomDocument;
		$xslDom->resolveExternals = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( Resourcer::getInstance( 'xslt' )->compile( $instance ) ) ) {
			throw new exception( "XSLT loader problem." );
		}
		$xslProc = new \XsltProcessor();
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
		}
		return true;
	}

	public function redirect( $url ) {

		$this->redirect = true;
		header( 'Location: ' . $url );
		die();
	}
}
