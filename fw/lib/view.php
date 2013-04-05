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
	 * @param int      $err
	 * @param bool|int $ttl
	 */
	public function httpError( $err, $ttl = false ) {

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
		if( $ttl and is_numeric( $ttl ) and $ttl >= 0 ) {
			self::addExpires( $ttl );
		}
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
	 * @param \DOMDocument  $xml
	 * @param bool|instance $specificInstance
	 * @param bool          $dontEcho
	 *
	 * @throws exception
	 * @internal param bool|string $instance
	 * @return bool|string
	 */
	public function render( &$xml, $specificInstance = false, $dontEcho = false ) {

		Debugger::addLine( 'Render start' );
		if( $this->error or $this->redirect ) {
			return false;
		}
		if( $specificInstance ) {
			$instance = $specificInstance;
		} elseif( $this->template ) {
			$instance = $this->template;
		} elseif( $this->instance ) {
			$instance = $this->instance;
		} else {
			$instance = 'main';
		}

		if( !$resource = Resourcer::getInstance( 'xslt' )->compile( $instance ) ) {
			throw new exception( "XSLT resource not found" );
		}

		$xslDom                     = new \DomDocument;
		$xslDom->resolveExternals   = true;
		$xslDom->substituteEntities = true;
		if( !$xslDom->loadXML( $resource ) ) {
			throw new exception( "XSLT load problem for instance '$instance'." );
		}

		$xslProc = new \XsltProcessor();
		$xslProc->importStyleSheet( $xslDom );

		Action::getInstance()->controller->fillXML();

		// transform template
		if( $html = $xslProc->transformToXml( $xml ) ) {
			if( $dontEcho ) {
				return $html;
			}
			echo $html;
			$this->rendered = true;
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
	 * Добавляет заголовки Expires и X-Accel-Expires
	 *
	 * @param $ttl
	 */
	public static function addExpires( $ttl ) {

		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', $ttl ? ( time() + $ttl ) : 0 ) );
		if( isset( $SERVER['SERVER_SOFTWARE'] ) and substr( $_SERVER['SERVER_SOFTWARE'], 0, 6 ) ) {
			// Установка X-Accel-Expires полезна, так как позволяет более точно контролировать кэш,
			// если время на веб-сервере и время на сервере, выполняющем скрипт, отличаются
			header( 'X-Accel-Expires: ' . ( $ttl ? $ttl : 'off' ) );
		}
	}
}
