<?php

namespace Difra;

/**
 * Class Exception
 * @package Difra
 */
class Exception extends \exception {

	/**
	 * Использование: в случае, если ловим исключение, которое мы не должны были поймать,
	 * вызываем $ex->notify() и на почту errors@a-jam.ru отправляется письмо с информацией об ошибке.
	 */
	public function notify() {

		self::notifyObj( $this );
	}

	/**
	 * @static
	 *
	 * @param \Difra\Exception|\exception $exception
	 */
	static public function notifyObj( $exception = null ) {

		if( !Debugger::getInstance()->isConsoleEnabled() ) {
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

			mail( 'errors@a-jam.ru', $_SERVER['HTTP_HOST'] . ': ' . $exception->getMessage(), $text );
		} else {
		}
	}
}
