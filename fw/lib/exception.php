<?php

namespace Difra;

class Exception extends \exception {

	public function __construct( $message = null, $code = 0, \Exception $previous = null ) {

		if( !Debugger::getInstance()->isEnabled() ) {
			$date = date( 'r' );
			$server = print_r( $_SERVER, true );
			$post = print_r( $_POST, true );
			$cookie = print_r( $_COOKIE, true );
			$user = Auth::getInstance()->data['email'];

			$text = <<<MSG
$message

Page:	{$_SERVER['REQUEST_URI']}
Time:	$date
Host:	{$_SERVER['HTTP_HOST']}
File:		{$this->getFile()}
Line:	{$this->getLine()}
User:	$user

{$this->getTraceAsString()}

\$_SERVER:
$server

\$_POST:
$post

\$_COOKIE:
$cookie
MSG;

			mail( 'errors@a-jam.ru', 'Report from ' . $_SERVER['HTTP_HOST'], $text );
			echo 'Error.';
		} else {
			parent::__construct( $message, $code, $previous );
		}
	}
}
