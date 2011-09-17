<?php

namespace Difra;

class Exception extends \exception {

	public function __construct( $message = null, $code = 0, \Exception $previous = null ) {

		$date = date( 'r' );
		$text = <<<MSG
$message

Time:	$date
Host:	{$_SERVER['HTTP_HOST']}
File:		{$this->getFile()}
Line:	{$this->getLine()}

{$this->getTraceAsString()}

MSG;

		if( !Debugger::getInstance()->isEnabled() ) {
			mail( 'errors@a-jam.ru', 'Report from ' . $_SERVER['HTTP_HOST'], $text );
			echo 'Error.';
		}
	}
}
