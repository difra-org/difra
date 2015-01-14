<?php
/*

use Difra\Param;

class AdmOrdersController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$node = $this->root->appendChild( $this->xml->createElement( 'ordersList' ) );
		\Difra\Plugins\Catalog\Orders::getInstance()->getListXML( $node, false );
	}

	public function archiveAction() {

		$node = $this->root->appendChild( $this->xml->createElement( 'ordersList' ) );
		\Difra\Plugins\Catalog\Orders::getInstance()->getListXML( $node, true );
	}

	public function viewAction( Param\AnyInt $id ) {

		$node = $this->root->appendChild( $this->xml->createElement( 'orderView' ) );
		\Difra\Plugins\Catalog\Orders::getInstance()->getOrderXML( $node, $id->val(), true );
	}

	public function updateAction( Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Orders::getInstance()->update( $id->val(), $_POST );
		$this->view->redirect( '/adm/orders' );
	}

	public function saveAction( Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Orders::getInstance()->modify( $id->val(), $_POST );
		$this->view->redirect( "/adm/orders/view/$id" );
	}
}
*/
