<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Class AdmDevelopmentLocalesController
 */
class AdmDevelopmentLocalesController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$localeNode = $this->root->appendChild( $this->xml->createElement( 'locales' ) );
		\Difra\Adm\Localemanage::getInstance()->getLocalesTreeXML( $localeNode );
	}
}