<?php

class Auth {

	public $logged = false;
	public $id = null;
	public $data = null;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		$this->_load();
	}

	public function getAuthXML( $node ) {

		$authNode = $node->appendChild( $node->ownerDocument->createElement( 'auth' ) );
		if( !$this->logged ) {
			$authNode->appendChild( $node->ownerDocument->createElement( 'unauthorized' ) );
			return false;
		} else {
			$subNode = $authNode->appendChild( $node->ownerDocument->createElement( 'authorized' ) );
			$subNode->setAttribute( 'id', $this->id );
		}
	}

	public function login( $id, $data = null ) {

		$this->id = $id;
		$this->data = $data;
		$this->logged = true;
		$this->_save();
	}

	public function logout() {

		$this->id = $this->data = null;
		$this->logged = false;
		$this->_save();
	}

	private function _save() {

		if( !isset( $_SESSION ) ) {
			session_start();
		}
		if( $this->logged ) {
			$_SESSION['auth'] = array(
				'id'	=> $this->id,
				'data'	=> $this->data
			);
		} else {
			if( isset( $_SESSION['auth'] ) ) {
				unset( $_SESSION['auth'] );
			}
		}
	}

	private function _load() {

		if( !isset( $_SESSION ) ) {
			session_start();
		}
		if( !isset( $_SESSION['auth'] ) ) {
			return false;
		}
		$this->id   = $_SESSION['auth']['id'];
		$this->data = $_SESSION['auth']['data'];
		return $this->logged = true;
	}

}
