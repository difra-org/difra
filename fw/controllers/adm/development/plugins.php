<?php

class AdmDevelopmentPluginsController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$pluginsNode = $this->root->appendChild( $this->xml->createElement( 'plugins' ) );
		\Difra\Plugger::getInstance()->getPluginsXML( $pluginsNode );
	}
}