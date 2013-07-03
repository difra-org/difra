<?php

namespace Difra\View;

use Difra\Envi;

class Exception extends \Exception {

	public function __construct( $message, $code = 0, \Exception $previous = null ) {

		//parent::__construct( $message, $code, $previous );

		$errors = include( 'http_errors.php' );
		if( isset( $errors[$message] ) ) {
			$err = $message;
			$error = $errors[$err];
		} elseif( isset( $errors[$code] ) ) {
			$err = $code;
			$error = $message ? $message : $errors[$code];
		} else {
			$err = 500;
			$error = $message;
		}

		header( "HTTP/1.1 $err $error" );
		/*
		if( $ttl and is_numeric( $ttl ) and $ttl >= 0 ) {
			self::addExpires( $ttl );
		}
		*/
		try {
			$xml = new \DOMDocument();
			/** @var $root \DOMElement */
			$root = $xml->appendChild( $xml->createElement( 'error' . $err ) );
			$root->setAttribute( 'host', \Difra\Site::getInstance()->getHost() );
			$root->setAttribute( 'hostname', $host = Envi::getHost() );
			$root->setAttribute( 'mainhost', $mainHost = Envi::getHost( true ) );
			if( $host != $mainHost ) {
				$root->setAttribute( 'urlprefix', 'http://' . $mainHost );
			}
			$root->setAttribute( 'build', \Difra\Site::getInstance()->getBuild() );
			$configNode = $root->appendChild( $xml->createElement( 'config' ) );
			\Difra\Site::getInstance()->getConfigXML( $configNode );
			\Difra\View::render( $xml, 'error_' . $err );
		} catch( exception $ex ) {
			echo( <<<ErrorPage
			<html>
			<head>
			<title>$error</title>
			</head>
			<body>
			<center><h1 style="padding:350px 0px 0px 0px">Error $err: $error</h1></center>
			$message
			</body>
			</html>
ErrorPage
			);
		}
		\Difra\View::$rendered = true;
		die();
	}
}