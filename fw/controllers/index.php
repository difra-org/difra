<?php

/**
 * Class IndexController
 */
class IndexController extends \Difra\Controller {

	public function indexAction() {

		$this->root->appendChild( $this->xml->createElement( 'index' ) );
	}
}