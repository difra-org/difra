<?php

namespace Difra;

class Ajax {

	public $isAjax = false;
	public $parameters = array();
	public $response = array();

	public function __construct() {

		$this->isAjax = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' );
		if( $this->isAjax ) {
			$this->parameters = $this->getRequest();
		}
	}

	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private function getRequest() {

		$res = array();
		if( !empty( $_POST['json'] ) ) {
			$res = json_decode( $_POST['json'], true );
		}
		return $res;
	}

	public function setResponse( $param, $value ) {

		$this->response[$param] = $value;
	}

	public function getResponse() {

		return json_encode( $this->response );
	}

	public function notify( $message ) {

		$this->setResponse( 'action', 'notify' );
		$this->setResponse( 'message', htmlspecialchars( $message, ENT_IGNORE, 'UTF-8' ) );
		$this->setResponse( 'lang', array(
						  'close'	=> Locales::getInstance()->getXPath( 'notifications/close' )
					    ) );
	}
}

