<?php

namespace Difra\View;

class Exception extends \Exception {

	public static $error = null;

	public function __construct( $message, $code = 0, \Exception $previous = null ) {

		//parent::__construct( $message, $code, $previous );

		if( isset( self::$errors[$message] ) ) {
			$err = $message;
			$error = self::$errors[$err];
			$msg = '';
		} elseif( isset( self::$errors[$code] ) ) {
			$err = $code;
			$error = self::$errors[$err];
			$msg = $message;
		} else {
			$err = 500;
			$error = self::$errors[$err];
			$msg = $message;
		}

		self::$error = $err;
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
			$root->setAttribute( 'host', \Difra\Envi::getSite() );
			$root->setAttribute( 'hostname', $host = \Difra\Envi::getHost() );
			$root->setAttribute( 'mainhost', $mainHost = \Difra\Envi::getHost( true ) );
			if( $host != $mainHost ) {
				$root->setAttribute( 'urlprefix', 'http://' . $mainHost );
			}
			$root->setAttribute( 'build', \Difra\Envi\Version::getBuild() );
			$configNode = $root->appendChild( $xml->createElement( 'config' ) );
			\Difra\Envi::getConfigXML( $configNode );
			\Difra\View::render( $xml, 'error_' . $err );
		} catch( \Difra\Exception $ex ) {
			echo( <<<ErrorPage
<html>
	<head>
		<title>$error</title>
	</head>
	<body>
		<center>
			<h1 style="padding:350px 0 0 0">Error $err: $error</h1>
			$msg
		</center>
	</body>
</html>
ErrorPage
			);
		}
		\Difra\View::$rendered = true;
		die();
	}

	public static $errors = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);
}