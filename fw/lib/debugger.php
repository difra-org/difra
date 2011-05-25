<?php

class Debugger {
	
	private $enabled = false;
	private $output = array();
	private $startTime;
	
	static public function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}
	
	public function __construct() {
		
		$this->startTime = microtime( true );
		ini_set( 'display_errors', 'Off' );
	}
	
	public function enable() {
		
		$this->enabled = true;
		ini_set( 'display_errors', 'On' );
		ini_set( 'error_reporting', E_ALL | E_STRICT );
		ini_set( 'html_errors', !empty( $_SERVER['REQUEST_METHOD'] ) ? 'On' : 'Off' );
	}
	
	public function isEnabled() {
		
		return $this->enabled;
	}
	
	public function addLine( $line ) {
		
		if( !$this->enabled ) {
			return;
		}
		$output[] = $line;
	}
	
	public function printOutput() {
		
		if( !$this->enabled ) {
			return;
		}
		echo "<!--\n";
		echo "Page rendered in " . ( microtime( true ) - $this->startTime )  . " seconds\n";
		echo implode( "\n", $this->output );
		echo "-->";
	}
}