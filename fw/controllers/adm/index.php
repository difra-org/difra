<?php

class AdmIndexController extends Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$indexNode = $this->root->appendChild( $this->xml->createElement( 'index' ) );
	}

}
