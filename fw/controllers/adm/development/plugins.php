<?php

class AdmDevelopmentPluginsController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$pluginsNode = $this->root->appendChild( $this->xml->createElement( 'plugins' ) );
		\Difra\Plugger::getInstance()->getPluginsXML( $pluginsNode );
	}

	public function enableAjaxAction( \Difra\Param\AnyString $name ) {

		if( !\Difra\Plugger::getInstance()->turnOn( $name->val() ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'adm/plugins/failed' ) );
		}
		$this->ajax->refresh();
	}

	public function disableAjaxAction( \Difra\Param\AnyString $name ) {

		if( !\Difra\Plugger::getInstance()->turnOn( $name->val() ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'adm/plugins/failed' ) );
		}
		$this->ajax->refresh();
	}
}