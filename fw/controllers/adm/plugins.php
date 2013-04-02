<?php

class AdmPluginsController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$pluginsNode = $this->root->appendChild( $this->xml->createElement( 'plugins' ) );
		$plugins     = \Difra\Plugger::getInstance()->smartPluginsEnable();
		\Difra\Libs\XML\DOM::array2domAttr( $pluginsNode, $plugins );
	}
}