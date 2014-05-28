<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Class AdmDevelopmentConfigController
 */
class AdmDevelopmentConfigController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		if( !\Difra\Debugger::isEnabled() ) {
			throw new \Difra\View\Exception( 404 );
		}
		$config = \Difra\Config::getInstance();
		/** @var \DOMElement $configNode */
		$configNode = $this->root->appendChild( $this->xml->createElement( 'configuration' ) );
		$conf = $config->getConfig();
		$configNode->setAttribute( 'current', var_export( $conf, true ) );
		$configNode->setAttribute( 'diff', $config->getTxtDiff() );
	}

	public function resetAjaxAction() {

		\Difra\Config::getInstance()->reset();
		$this->ajax->notify( $this->locale->getXPath( 'adm/config/reset-done' ) );
		$this->ajax->refresh();
	}
}