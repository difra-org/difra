<?php

class Ajax {

	public $isAjax = false;
	public $parameters = array();
	public $response = array();

	public function __construct() {

		$this->isAjax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		if( $this->isAjax ) {
			$this->parameters = $this->getRequest();
		}
	}

	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private function getRequest() {

		return !empty( $_POST['json'] ) ? json_decode( $_POST['json'], true ) : array();
	}

	public function setResponse( $param, $value ) {

		$this->response[$param] = $value;
	}

	public function getResponse() {

		return json_encode( $this->response );
	}
}

