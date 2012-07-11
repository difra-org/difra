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
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

	}

	public function httpError( $err ) {

		if( $this->redirect or $this->error ) {
			return;
		}
		$errors = include ( 'view/http_errors.php' );

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
			$root->setAttribute( 'hostname', Site::getInstance()->getHostname() );
			$root->setAttribute( 'mainhost', Site::getInstance()->getMainhost() );
			if( Site::getInstance()->getHostname() != Site::getInstance()->getMainhost() ) {
				$root->setAttribute( 'urlprefix', 'http://' . Site::getInstance()->getMainhost() );
			}
			$root->setAttribute( 'build', Site::getInstance()->getBuild() );
			$configNode = $root->appendChild( $xml->createElement( 'config' ) );
			Site::getInstance()->getConfigXML( $configNode );
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
		if( $errorPage ) {
			$hasTemplate = false;
			$li = $xslDom->documentElement->childNodes;
			foreach( $li as $el ) {
				if( $el->nodeName == 'xsl:template' ) {
					$hasTemplate = true;
					break;
				}
			}
			if( !$hasTemplate ) {
				throw new Exception( 'Error page template not found' );
			}
		}
		$xslProc = new \XsltProcessor();
		$xslProc->resolveExternals = true;
		$xslProc->substituteEntities = true;
		$xslProc->importStyleSheet( $xslDom );

		// transform template
		if( $html = $xslProc->transformToDoc( $xml ) ) {
			$this->postProcess( $html, $xml );
			$html->formatOutput = Debugger::getInstance()->isEnabled();
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

	/**
	 * @param \DOMDocument $html
	 * @param \DOMDocument $xml
	 */
	private function postProcess( $html, $xml ) {

		$htmlRoot = $html->documentElement;
		if( $htmlRoot->nodeName != 'html' ) {
			return;
		}
		$xmlRoot = $xml->documentElement;
		// Добавление классов на основе User-Agent
		if( $xmlRoot->hasAttribute( 'uaClass' ) ) {
			$uac = $xmlRoot->getAttribute( 'uaClass' );
			if( $htmlRoot->hasAttribute( 'class' ) ) {
				$uac = $htmlRoot->getAttribute( 'class' ) . ' ' . $uac;
			}
			$uac = trim( $uac );
			if( $uac ) {
				$htmlRoot->setAttribute( 'class', $uac );
			}
		}
		// Добавление объекта config для js
		$config = '';
		if( $conf = $xmlRoot->getElementsByTagName( 'config' ) and $conf = $conf->item( 0 ) ) {
			if( $body = $htmlRoot->getElementsByTagName( 'body' ) and $body = $body->item(0) ) {
				$attrs = $conf->attributes;
				if( !empty( $attrs ) ) {
					foreach( $attrs as $attr ) {
						$config .= "config.{$attr->name}='" . addslashes( $attr->value ) . "';";
					}
				}
				if( $config ) {
					$scriptNode = $body->insertBefore( $html->createElement( 'script' ), $body->firstChild );
					$scriptNode->setAttribute( 'type', 'text/javascript' );
					$scriptNode->appendChild( $html->createComment( " var config={};" . $config . " " ) );
				}
			}
		}
	}
}
