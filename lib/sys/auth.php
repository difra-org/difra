<?php

class Auth {

	public $logged = false;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {
	}

	public function getAuthXML( $node ) {

		$authNode = $node->appendChild( $node->ownerDocument->createElement( 'auth' ) );
		if( !$this->logged ) {
			$authNode->appendChild( $node->ownerDocument->createElement( 'unauthorized' ) );
			return false;
		}
	}
}
