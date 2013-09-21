<?php

class AdmContentPortfolioIndexController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$search = new \Difra\Unify\Search( 'PortfolioEntry' );
		$search->getListXML( $this->root );
	}

	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'PortfolioEntryAdd' ) );
	}
}