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

	public function saveAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxHTML $description, \Difra\Param\AjaxString $release = null,
					\Difra\Param\AjaxString $link = null, \Difra\Param\AjaxString $software = null ) {
	}
}