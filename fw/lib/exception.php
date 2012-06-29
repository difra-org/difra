<?php

namespace Difra;

class Exception extends \exception {
}

// TODO: повесить это дело на событие
if( !function_exists( 'Difra\\exceptionHandler' ) ) {
	/**
	 * @param \Difra\Exception $exception
	 */
	function exceptionHandler( $exception )
	{

		if( !Debugger::getInstance()->isEnabled() ) {
			$date   = date( 'r' );
			$server = print_r( $_SERVER, true );
			$post   = print_r( $_POST, true );
			$cookie = print_r( $_COOKIE, true );
			$user   = Auth::getInstance()->data['email'];

			$text = <<<MSG
{$exception->getMessage()}

Page:	{$_SERVER['REQUEST_URI']}
Time:	$date
Host:	{$_SERVER['HTTP_HOST']}
File:		{$exception->getFile()}
Line:	{$exception->getLine()}
User:	$user

{$exception->getTraceAsString()}

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
//			if( method_exists( $exception, 'callPrevious' ) ) {
//				$exception->callPrevious();
//			}
		}
	}
	set_exception_handler( 'Difra\\exceptionHandler' );
}
