<?php
/*

use Difra\Param;

class OrdersController extends Difra\Controller {

	public function dispatch() {

		$this->action->dispatch( 'catalog', 'categories.php' );
		$this->root->setAttribute( 'favorites', \Difra\Plugins\Catalog\Cart\Favourites::getInstance()->getCountLocale() );
		$this->root->setAttribute( 'cart', \Difra\Plugins\Catalog\Cart::getInstance()->getCountLocale() );
	}

	public function indexActionAuth() {

		$this->_orders( false );
	}

	public function closedActionAuth() {

		$this->_orders( 'closed' );
	}

	private function _orders( $sort ) {

		$ordersNode = $this->root->appendChild( $this->xml->createElement( 'ordersList' ) );
		$sorts = array( 'текущие' => '/orders', 'выполненные' => '/orders/closed' );
		foreach( $sorts as $k => $v ) {
			$sortNode = $ordersNode->appendChild( $this->xml->createElement( 'sort' ) );
			$sortNode->setAttribute( 'name', $k );
			$sortNode->setAttribute( 'href', $v );
			if( $_SERVER['REQUEST_URI'] == $v ) {
				$ordersNode->setAttribute( 'sort', $k );
			}
		}

		\Difra\Plugins\Catalog\Orders::getInstance()->getListXML( $ordersNode, $sort, $this->auth->id );
	}
	
	public function submodifyAction() {
		
		if( sizeof( $this->action->parameters ) == 1 and is_numeric( $this->action->parameters[0] )
		   and isset( $_POST['key'] ) and is_numeric( $_POST['key'] )
		   and isset( $_POST['quanity'] ) and is_numeric( $_POST['key'] ) ) {
			
			$orderId = intval( array_shift( $this->action->parameters ) );
			$position = intval( $_POST['key'] );
			$quanity = intval( $_POST['quanity'] );
			$exts = !empty( $_POST['info'] ) ? $_POST['info'] : false;
			
			\Difra\Plugins\Catalog\Orders::getInstance()->userModify( $orderId, $position, $quanity, $exts );
		}
		
		$this->view->redirect( '/orders' );
	}
	
	public function subdeleteAction( Param\AnyInt $orderId, Param\AnyInt $position ) {
		
		\Difra\Plugins\Catalog\Orders::getInstance()->userDeletePosition( (int)(string)$orderId, (int)(string)$position );
		$this->view->redirect( '/orders' );
	}
}
*/