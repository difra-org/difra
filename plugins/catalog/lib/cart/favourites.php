<?php
/*

namespace Difra\Plugins\Catalog\Cart;

class Favourites {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private $list = array();
	private $changed = false;

	public function __construct() {

		if( !isset( $_SESSION ) ) {
			session_start();
		}
		$this->_load();
	}

	public function __destruct() {

		if( $this->changed ) {
			$this->_save();
		}
	}


	public function _load() {

		if( !isset( $_SESSION['favourites'] ) ) {
			$_SESSION['favourites'] = array();
		}
		$this->list = $_SESSION['favourites'];
		if( empty( $_SESSION['favourites_logged'] ) and \Difra\Auth::getInstance()->logged ) {
			$_SESSION['favourites_logged'] = true;
			$db = \Difra\MySQL::getInstance();
			$data = $db->fetch( 'SELECT `favourites` FROM `catalog_favourites` WHERE `user`=\'' . $db->escape( \Difra\Auth::getInstance()->data['id'] ) . "'" );
			if( !empty( $data ) ) {
				$data = @unserialize( $data[0]['favourites'] );
				if( !empty( $data ) ) {
					foreach( $data as $item ) {
						$this->_add( $item );
					}
				}
			}
			$this->changed = true;
		}
		if( !empty( $_SESSION['favourites_logged'] ) and !\Difra\Auth::getInstance()->logged ) {
			$_SESSION['favourites_logged'] = '';
			$this->clear( true );
			$this->changed = true;
		}
	}

	public function _save() {

		$_SESSION['favourites'] = $this->list;
		if( \Difra\Auth::getInstance()->logged ) {
			$db = \Difra\MySQL::getInstance();
			$data = $db->escape( serialize( $this->list ) );
			$db->query( 'REPLACE LOW_PRIORITY INTO `catalog_favourites` (`user`,`favourites`) VALUES (\'' . $db->escape( \Difra\Auth::getInstance()->data['id'] ) . "','$data')" );
		}
	}

	public function _add( $item ) {

		if( !empty( $this->list ) ) {
			foreach( $this->list as $v ) {
				if( $item->id == $v->id ) {
					return false;
				}
			}
		}
		$this->list[] = $item;
		return $this->changed = true;
	}

	public function add( $id ) {

		$item = new Item( $id );
		$this->_add( $item );
	}

	public function getList() {

		return $this->list;
	}

	/*
	public function getListXML( $node ) {

		if( empty( $this->list ) ) {
			return false;
		}

		// precache
		$ids = array();
		foreach( $this->list as $item ) {
			$ids[] = $item->id;
		}
		
		// get xml
		foreach( $this->list as $item ) {
			$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			$item->getXML( $itemNode );
		}
	}
	 * /

	public function getCount() {

		return sizeof( $this->list );
	}

	public function getCountLocale() {

		$n = $this->getCount();
		if( $n % 10 == 0 ) {
			return "$n товаров";
		} elseif( $n > 10 and $n < 20 ) {
			return "$n товаров";
		} elseif( $n % 10 == 1 ) {
			return "$n товар";
		} elseif( $n % 10 < 5 ) {
			return "$n товара";
		} else {
			return "$n товаров";
		}
	}

	public function clear() {

		$_SESSION['favourites'] = array();
		$this->changed = true;
	}

	public function isAdded( $id ) {

		foreach( $this->list as $item ) {
			if( $item->id == $id ) {
				return true;
			}
		}
		return false;
	}

	public function remove( $id ) {

		foreach( $this->list as $k=>$item ) {
			if( $item->id == $id ) {
				unset( $this->list[$k] );
				$this->changed = true;
			}
		}
	}

	public function getIDs() {

		$ids = array();
		if( !empty( $this->list ) ) {
			foreach( $this->list as $item ) {
				$ids[] = $item->id;
			}
		}
		return array_unique( $ids );
	}

}
*/
