<?php
/*
namespace Difra\Plugins\Catalog;

class Orders {

	/**
	 * @static
	 * @return Orders
	 * /
	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	const ORDER_ADD_UNAUTH   = 'unauth';

	/**
	 * Добавление заказа
	 * @param array $data
	 * @return bool|string
	 * /
	public function add( $data ) {

		$auth = \Difra\Auth::getInstance();
		if( !$auth->logged ) {
			return self::ORDER_ADD_UNAUTH;
		}
		$db = \Difra\MySQL::getInstance();
		$cart = Cart::getInstance();
		$cost = $cart->getTotal();
		$cartCont = $cart->getList();
		if( empty( $cartCont ) ) {
			return false;
		}
		$db->query( "INSERT INTO `catalog_orders` (`user_id`,`user_email`,`cost`,`cart`) VALUES ('" . $db->escape( $auth->data['id'] ) . "','" . $db->escape( $auth->data['email'] ) . "',$cost,'" . $db->escape( serialize( $cartCont ) ) . "')" );
		$id = $db->getLastId();
		foreach( $data as $k => $v ) {
			$db->query( "INSERT INTO `catalog_orders_data` (`order_id`,`name`,`value`) VALUES ($id,'" . $db->escape( $k ) . "','" . $db->escape( $v ) . "')" );
		}
		// TO DO: убрать бы ссылку на плагин
		\Difra\Plugins\Users::getInstance()->setInfo( array(
			'name' => $data['name'],
			'city' => $data['city'],
			'address' => $data['address'],
			'phone' => $data['phone']
		) );
		return true;
	}

	public function getList( $archive = null, $user = null ) {

		$where = '';
		if( $archive === true ) {
			$where = ' WHERE `closed`=1';
		} elseif( $archive === false ) {
			$where = ' WHERE `closed`=0';
		} elseif( $archive === 'closed' ) {
			$where = ' WHERE `closed`=1';
		}
		$db = \Difra\MySQL::getInstance();
		if( $user ) {
			$where = ( $where ? $where . ' AND ' : ' WHERE ' ) . "`user_email`='" . $db->escape( $user ) . "'";
		}
		$data = $db->fetch( 'SELECT * FROM `catalog_orders` ' . $where );
		foreach( $data as $k => $order ) {
			$addData = $db->fetch( 'SELECT `name`,`value` FROM `catalog_orders_data` WHERE `order_id`=' . $order['id'] );
			foreach( $addData as $add ) {
				$data[$k][$add['name']] = $add['value'];
			}
		}
		return $data;
	}

	public function getListXML( $node, $archive = null, $user = null ) {

		$data = $this->getList( $archive, $user );
		$this->_toXML( $node, $data );
	}

	public function getOrderXML( $node, $id, $updateExts = false ) {

		$data = $this->getOrder( $id );
		$this->_toXML( $node, $data, $updateExts );
	}

	public function getOrder( $id ) {

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch( "SELECT * FROM `catalog_orders` WHERE `id`='" . $db->escape( $id ) . "'" );
		foreach( $data as $k => $order ) {
			$addData = $db->fetch( 'SELECT `name`,`value` FROM `catalog_orders_data` WHERE `order_id`=' . $order['id'] );
			foreach( $addData as $add ) {
				$data[$k][$add['name']] = $add['value'];
			}
		}
		return $data;
	}

	private function _toXML( $node, $data, $updateExts = false ) {

		foreach( $data as $order ) {
			$orderNode = $node->appendChild( $node->ownerDocument->createElement( 'order' ) );
			foreach( $order as $k => $v ) {
				switch( $k ) {
				case 'cart':
					$cart = unserialize( $v );
					$cartNode = $orderNode->appendChild( $node->ownerDocument->createElement( 'cart' ) );
					if( !empty( $cart ) ) {
						foreach( $cart as $k=>$item ) {
							$subNode = $cartNode->appendChild( $node->ownerDocument->createElement( 'item' ) );
							$subNode->setAttribute( 'key', $k );
							if( $updateExts ) {
								$item->updateExts();
							}
							$item->getXML( $subNode );
						}
					}
					break;
				case 'date':
					$orderNode->setAttribute( 'date', \Difra\Locales::getInstance()->getDateFromMysql( $v ) );
					break;
				default:
					$orderNode->setAttribute( str_replace( '-', '_', $k ), $v );
				}
			}
		}
	}

	public function update( $id, $data ) {

		$pairs = array();
		$validKeys = array( 'weight', 'delivery_cost', 'comment', 'locked', 'paid', 'wait_arrival', 'packed', 'sent', 'closed' );
		$db = \Difra\MySQL::getInstance();
		foreach( $data as $k => $v ) {
			if( in_array( $k, $validKeys ) ) {
				$pairs[] = '`' . $db->escape( $k ) . "`='" . $db->escape( $v ) . "'";
			}
		}
		if( !empty( $pairs ) ) {
			$db->query( 'UPDATE `catalog_orders` SET ' . implode( ',', $pairs ) . " WHERE `id`='" . $db->escape( $id ) . "'" );
		}
	}

	public function modify( $id, $data ) {

		$db = \Difra\MySQL::getInstance();
		$id = $db->escape( $id );
		$cart = $db->fetch( "SELECT `cart` FROM `catalog_orders` WHERE `id`='$id'" );
		if( empty( $cart ) ) {
			return;
		}
		$cart = unserialize( $cart[0]['cart'] );
		if( !empty( $data['count'] ) ) {
			foreach( $data['count'] as $k=>$v ) {
				$cart[$k]->count = $v;
			}
		}
		if( !empty( $data['delete'] ) ) {
			foreach( $data['delete'] as $k=>$v ) {
				if( $v == 1 ) {
					unset( $cart[$k] );
				}
			}
		}
		if( !empty( $data['info'] ) ) {
			foreach( $data['info'] as $n => $inf ) {
				if( isset( $cart[$n] ) ) { // вдруг мы только что удалили этот пункт
					$cart[$n]->updateExts();
					$cart[$n]->changeExtsByName( $inf );
				}
			}
		}
		$cost = 0;
		foreach( $cart as $item ) {
			$item->updateExts();
			$cost += $item->price * $item->count;
		}
		$db->query( "UPDATE `catalog_orders` SET `cart`='" . $db->escape( serialize( $cart ) ) . "',`cost`='$cost' WHERE `id`='$id'" );
	}
	
	public function userModify( $orderId, $position, $quanity, $data = false ) {
		
		$db = \Difra\MySQL::getInstance();
		
		$orderId = $db->escape( $orderId );
		$userId  = \Difra\Auth::getInstance()->getId();
		$cart = $db->fetchOne( "SELECT `cart` FROM `catalog_orders` WHERE `id`='$orderId' AND `user_id`='$userId' AND `locked`='0'" );
		if( !$cart ) {
			return;
		}
		$cart = unserialize( $cart );
		
		$cart[$position]->count = $quanity;
		
		if( $data ) {
			$cart[$position]->updateExts();
			$cart[$position]->changeExtsByName( $data );
		}
		
		$cost = 0;
		foreach( $cart as $item ) {
			$item->updateExts();
			$cost += $item->price * $item->count;
		}
		$db->query( "UPDATE `catalog_orders` SET `cart`='" . $db->escape( serialize( $cart ) ) . "',`cost`='$cost' WHERE `id`='$orderId'" );
	}
	
	public function userDeletePosition( $orderId, $position ) {
		
		$db = \Difra\MySQL::getInstance();
		
		$orderId = $db->escape( $orderId );
		$userId  = \Difra\Auth::getInstance()->getId();
		$cart = $db->fetchOne( "SELECT `cart` FROM `catalog_orders` WHERE `id`='$orderId' AND `user_id`='$userId' AND `locked`='0'" );
		if( !$cart ) {
			return;
		}
		$cart = unserialize( $cart );
		
		unset( $cart[$position] );
		
		if( !empty( $cart ) ) {
			$cost = 0;
			foreach( $cart as $item ) {
				$item->updateExts();
				$cost += $item->price * $item->count;
			}
			$db->query( "UPDATE `catalog_orders` SET `cart`='" . $db->escape( serialize( $cart ) ) . "',`cost`='$cost' WHERE `id`='$orderId'" );
		} else {
			$db->query( "DELETE FROM `catalog_orders` WHERE `id`='$orderId'" );
		}
	}
}
*/