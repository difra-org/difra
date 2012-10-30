<?php

class AdmConfigController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		if( !\Difra\Debugger::getInstance()->isEnabled() ) {
			$this->view->httpError( 404 );
			return;
		}
		$config     = \Difra\Config::getInstance();
		$configNode = $this->root->appendChild( $this->xml->createElement( 'configuration' ) );
		$conf       = $config->getConfig();
		$configNode->setAttribute( 'current', var_export( $conf, true ) );
		$configNode->setAttribute( 'diff', $config->getTxtDiff() );
	}

	public function resetAjaxAction() {

		\Difra\Config::getInstance()->reset();
		$this->ajax->notify( $this->locale->getXPath( 'adm/config/reset-done' ) );
		$this->ajax->refresh();
	}
}