<?php

class AdmIndexController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$indexNode = $this->root->appendChild( $this->xml->createElement( 'index' ) );
		\Difra\Adm\Stats::getInstance()->getXML( $indexNode );
	}

}
