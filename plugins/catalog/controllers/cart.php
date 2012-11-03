<?php
/*
use Difra\Param;

class CartController extends Difra\Controller {

	public function clearAction() {

		\Difra\Plugins\Catalog\Cart::getInstance()->clear();
		return $this->view->redirect( \Difra\Plugins\Catalog::getInstance()->lastPage );
	}

	public function addAction() {

		if( !isset( $_POST['id'] ) or !is_numeric( $_POST['id'] ) ) {
			throw new exception( 'CartController::addAction expects $_POST[\'id\']' );
		}
		\Difra\Plugins\Catalog\Cart::getInstance()->add( $_POST['id'], !empty( $_POST['view_ext'] ) ? $_POST['view_ext'] : array() );
		$this->view->redirect( \Difra\Plugins\Catalog::getInstance()->lastPage );
	}

	public function viewAjaxAction() {

		$cartNode = $this->root->appendChild( $this->xml->createElement( 'cart' ) );
		$cart = \Difra\Plugins\Catalog\Cart::getInstance();
		$cart->getListXML( $cartNode );
		$cartNode->setAttribute( 'total', $cart->getTotal() );
		\Difra\Plugins\Users::getInstance()->getInfoXML( $cartNode );
		$this->ajax->setResponse( 'html', $this->view->render( $this->xml, 'catalog_cart', true ) );
	}
	
	public function orderAjaxAction() {

		$res = \Difra\Plugins\Catalog\Orders::getInstance()->add( $this->ajax->parameters );
		if( $res === true ) {
			\Difra\Plugins\Catalog\Cart::getInstance()->clear();
			$this->ajax->setResponse( 'error', 0 );
		} else {
			$this->ajax->setResponse( 'error', $res );
		}
	}

	public function setnumAjaxAction( Param\AnyInt $key, Param\AnyInt $num ) {

		$cart = \Difra\Plugins\Catalog\Cart::getInstance();
		$cart->setNum( (int)(string)$key, (int)(string)$num );
		// TO DO: выводить это через Ajax
		$this->output = json_encode( array( 'cart' => $cart->getCountLocale(), 'total' => $cart->getTotal() ) );
		return;
	}

	public function delAjaxAction( Param\AnyInt $id ) {

		$cart = \Difra\Plugins\Catalog\Cart::getInstance();
		$cart->removeByKey( (int)(string)$id );
		$this->output = json_encode( array( 'cart' => $cart->getCountLocale(), 'total' => $cart->getTotal() ) );
		return;
	}

}
*/