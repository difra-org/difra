<?php
/*
use Difra\Param;

class FavouritesController extends Difra\Controller {

	public function indexAction() {

		\Difra\Plugins\Catalog::getInstance()->setLastPage( $this->action->uri );
		$this->root->setAttribute( 'favorites', \Difra\Plugins\Catalog\Cart\Favourites::getInstance()->getCountLocale() );
		$this->root->setAttribute( 'cart', \Difra\Plugins\Catalog\Cart::getInstance()->getCountLocale() );
		$ids = \Difra\Plugins\Catalog\Cart\Favourites::getInstance()->getIDs();
		if( !empty( $ids ) ) {
			$indexNode = $this->root->appendChild( $this->xml->createElement( 'index' ) );
			$catalogNode = $indexNode->appendChild( $this->xml->createElement( 'catalog' ) );
			$catalogNode->setAttribute( 'index', 'yes' );
			\Difra\Plugins\Catalog::getInstance()->getGoodiesXML( $catalogNode, false, false, false, false, $ids );
		} else {
			$this->root->appendChild( $this->xml->createElement( 'favempty' ) );
		}
	}

	public function addAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Cart\Favourites::getInstance()->add( (int)(string)$id );
		$this->output = '1';
	}

	public function delAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Cart\Favourites::getInstance()->remove( (int)(string)$id );
		$this->output = '0';
	}

	public function countAjaxAction() {

		$this->output = \Difra\Plugins\Catalog\Cart\Favourites::getInstance()->getCountLocale();
	}


}

*/