<?php

class IndexController extends Difra\Controller {

	public function dispatch() {

		// this code will be called before action
	}

	public function indexAction() {

		$indexNode = $this->root->appendChild( $this->xml->createElement( 'index' ) );
	}

}
