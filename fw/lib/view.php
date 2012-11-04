<?php

namespace Difra;

class View {

	public $error = false;
	public $redirect = false;
	public $rendered = false;
	public $template = false;
	public $instance = 'main';

	/**
	 * Синглтон
	 * @return View
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Завершение выполнения с выводом http-ошибки
	 * Пробует отрендерить шаблон error_xxx, где xxx — номер ошибки, после чего выводит простенькую страничку с ошибкой.
	 *
	 * @param $err
	 */
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
		$this->rendered = true;
		try {
			$xml = new \DOMDocument();
			/** @var $root \DOMElement */
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
			$this->render( $xml, 'error_' . $err );
			$this->error = $err;
		} catch( exception $ex ) {
			$this->error = $err;
			echo( <<<ErrorPage
			<html>
			<head>
			<title>$error</title>
			</head>
			<body>
			<center><h1 style="padding:350px 0px 0px 0px">Error $err: $error</h1></center>
			</body>
			</html>
ErrorPage
			);
		}
		die();
	}

	/**
	 * @param \DOMDocument $xml
	 * @param bool|string  $instance
	 * @param bool         $dontEcho
	 *
	 * @throws exception
	 * @return bool|string
	 */
	public function render( $xml, $instance = false, $dontEcho = false ) {

		if( $this->error or $this->redirect ) {
			return false;
		}
		if( $instance ) {
		} elseif( $this->template ) {
			$instance = $this->template;
		} elseif( $this->instance ) {
			$instance = $this->instance;
		} else {
			$instance = 'main';
		}

		$xslDom                     = new \DomDocument;
		$xslDom->resolveExternals   = true;
		$xslDom->substituteEntities = true;
		$resource                   = Resourcer::getInstance( 'xslt' )->compile( $instance );
		if( !$xslDom->loadXML( $resource ) ) {
			throw new exception( "XSLT load problem for instance '$instance'." );
		}
		$xslProc                     = new \XsltProcessor();
		$xslProc->resolveExternals   = true;
		$xslProc->substituteEntities = true;
		$xslProc->importStyleSheet( $xslDom );

		// transform template
		if( $html = $xslProc->transformToDoc( $xml ) ) {
			$this->postProcess( $html, $xml, $instance );
			$html->formatOutput = Debugger::getInstance()->isEnabled();
			if( $dontEcho ) {
				return $html->saveXML();
			}
			// эта строка ломает CKEditor, поэтому она накакзана
			//header( 'Content-Type: application/xhtml+xml; charset=UTF-8' );
			echo( $html->saveXML() );
			if( !$dontEcho ) {
				$this->rendered = true;
			}
			if( function_exists( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			}
		} else {
			throw new exception( "Can't render templates" );
		}
		return true;
	}

	/**
	 * Редирект
	 * @param $url
	 */
	public function redirect( $url ) {

		$this->redirect = true;
		header( 'Location: ' . $url );
		die();
	}

	/**
	 * Пост-обработка HTML-страницы
	 *
	 * @param \DOMDocument $html
	 * @param \DOMDocument $xml
	 * @param string       $instance
	 */
	private function postProcess( $html, $xml, $instance ) {

		if( !$htmlRoot = $html->documentElement ) {
			return;
		}
		if( $htmlRoot->nodeName != 'html' ) {
			return;
		}
		// Добавление дебаг-консоли
		// Ворнинг: это дело зависит от VHOST_DEVMODE, а не от галочки debug.
		if( Debugger::getInstance()->isConsoleEnabled() and
		    $instance != 'debug' and !Action::getInstance()->controller->ajax->isAjax
		) {
			if( $htmlRoot->nodeName == 'html' ) {
				$headList = $htmlRoot->getElementsByTagName( 'head' );
				if( $headList->length ) {
					$build = Site::getInstance()->getBuild();
					$head  = $headList->item( 0 );
					/** @var $consoleCSS \DOMElement */
					$consoleCSS = $head->appendChild( $html->createElement( 'link' ) );
					$consoleCSS->setAttribute( 'type', 'text/css' );
					$consoleCSS->setAttribute( 'rel', 'stylesheet' );
					$consoleCSS->setAttribute( 'href', '/css/console.css?' . $build );
					/** @var $consoleJS \DOMElement */
					$consoleJS = $head->appendChild( $html->createElement( 'script' ) );
					$consoleJS->setAttribute( 'type', 'text/javascript' );
					$consoleJS->setAttribute( 'src', '/js/console.js?' . $build );
				}
				$bodyList = $htmlRoot->getElementsByTagName( 'body' );
				if( $bodyList->length ) {
					$body   = $bodyList->item( 0 );
					$ins    = Debugger::getInstance()->debugHTML();
					$debdom = new \DOMDocument();
					$debdom->loadXML( $ins );
					$body->appendChild( $html->importNode( $debdom->documentElement, true ) );
				}
			}
		}
		// Добавление классов на основе User-Agent
		if( $uac = Site::getInstance()->getUserAgentClass() ) {
			if( $htmlRoot->hasAttribute( 'class' ) ) {
				$uac = $htmlRoot->getAttribute( 'class' ) . ' ' . $uac;
			}
			$uac = trim( $uac );
			$htmlRoot->setAttribute( 'class', $uac );
		}
		// Добавление объекта config для js
		if( $body = $htmlRoot->getElementsByTagName( 'body' ) and $body = $body->item( 0 ) ) {
			$config = Site::getInstance()->getConfig();
			$confJS = '';
			foreach( $config as $k => $v ) {
				$confJS .= "config.{$k}='" . addslashes( $v ) . "';";
			}
			/** @var $scriptNode \DOMElement */
			$scriptNode = $body->insertBefore( $html->createElement( 'script' ), $body->firstChild );
			$scriptNode->setAttribute( 'type', 'text/javascript' );
			$scriptNode->appendChild( $html->createTextNode( "var config={};" . $confJS ) );
		}
	}
}
