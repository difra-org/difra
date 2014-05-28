<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Class IndexController
 */
class IndexController extends \Difra\Controller {

	public function indexAction() {

		$this->root->appendChild( $this->xml->createElement( 'index' ) );
	}
}