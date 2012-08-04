<?php

namespace Difra;

class Auth {

	public $logged = false;
	public $id = null;
	public $data = null;
	public $moderator = false;
	public $additionals = null;

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
			return;
		} else {
			$subNode = $authNode->appendChild( $node->ownerDocument->createElement( 'authorized' ) );
			$subNode->setAttribute( 'id', $this->id );
			$subNode->setAttribute( 'userid', $this->getId() );
			$subNode->setAttribute( 'moderator', $this->moderator );
			if( !empty( $this->additionals ) ) {
				foreach( $this->additionals as $k => $v ) {
					$subNode->setAttribute( $k, $v );
				}
			}
		}
	}

	public function login( $id, $data = null, $additionals = null ) {

		$this->id = $id;
		$this->data = $data;
		$this->additionals = $additionals;
		$this->logged = true;
		$this->_save();
	}

	public function logout() {

		$this->id = $this->data = $this->additionals = null;
		$this->logged = false;
		$this->_save();
	}

	public function update() {

		$this->_save();
	}

	private function _save() {

		Site::getInstance()->sessionStart();
		if( $this->logged ) {
			$_SESSION['auth'] = array(
				'id'	=> $this->id,
				'data'	=> $this->data,
				'additionals' => $this->additionals
			);
		} else {
			if( isset( $_SESSION['auth'] ) ) {
				unset( $_SESSION['auth'] );
			}
		}
	}

	private function _load() {

		Site::getInstance()->sessionLoad();
		if( !isset( $_SESSION['auth'] ) ) {
			return false;
		}
		$this->id   = $_SESSION['auth']['id'];
		$this->data = $_SESSION['auth']['data'];
		$this->additionals = $_SESSION['auth']['additionals'];
		$this->moderator = ( $_SESSION['auth']['data']['moderator'] == 1 ) ? true : false;
		return $this->logged = true;
	}

	/**
	 * Возвращает id текущего пользователя или null
	 * @return int|null
	 */
	public function getId() {
		
		return isset( $this->data['id'] ) ? $this->data['id'] : null;
	}
	
	public function isLogged() {
		
		return $this->logged;
	}
	
	/**
	 * Бросает exception, если пользователь не авторизован
	 */
	public function required() {
	
		if( !$this->logged ) {
			throw new exception( 'Authorization required' );
		}
	}

	public function setAdditionals( $additionals ) {
		
		$this->additionals = $additionals;
		$this->_save();
	}

	public function getAdditionals() {
		return $this->additionals;
	}

	public function isModerator() {
		return $this->moderator;
	}

}
