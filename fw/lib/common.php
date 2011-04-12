<?php

class ErrorHandler {
	public $error = false;
	private $setFlag;
	private $oldHandler;

	public function __construct() {

	}

	static function getInstance() {

		static $_instance = null;
		if( !$_instance ) {
			$_instance = new self( );
		}
		return $_instance;
	}

	public function handler( $errno, $errstr, $errfile, $errline ) {

		$this->error = true;
		throw new exception( "$errstr in $errfile:$errline" );
		return true;
	}

	public function set() {

		//		function ErrorHandlerWorkaround( $errno, $errstr, $errfile, $errline ) {
		//			return ErrorHandler::getInstance()->Handler( $errno, $errstr, $errfile, $errline );
		//		}
		$this->error = false;
		if( !$this->setFlag ) {
			$this->oldHandler = set_error_handler( array( 'errorHandler', 'handler' ) );
			$this->setFlag = true;
		}
	}

	public function get() {

		$e = $this->error;
		$this->error = false;
		if( $this->setFlag ) {
			set_error_handler( $this->oldHandler );
		}
		$this->setFlag = false;
		return $e;
	}
}

