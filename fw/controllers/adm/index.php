<?php

class AdmIndexController extends Controller {

	public function dispatch() {

		$this->view->template = 'adm';
		$this->view->menu = 'menu-adm.xml';
	}

	public function indexAction() {

		$indexNode = $this->root->appendChild( $this->xml->createElement( 'index' ) );
	}

}
