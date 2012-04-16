<?php

class AdmLocalesController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$localeNode = $this->root->appendChild( $this->xml->createElement( 'locales' ) );
		$tree = \Difra\Adm\Localemanage::getInstance()->getLocalesTree();
	}
}