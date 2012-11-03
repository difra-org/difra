<?php
/*

namespace Difra\Plugins\Catalog\Cart;
use Difra\Plugins\Catalog;

class Item {

	public $id = null;
	public $count = 1;
	public $ext = array();
	public $data = array();
	public $price = 0;

	public function __construct( $id, $ext = array(), $count = 1 ) {

		$this->id = intval( $id );
		$this->count = $count;
		$this->data = Catalog::getInstance()->getGoody( $id );
		if( empty( $this->data ) ) {
			throw new \Difra\Exception( "Can't find catalog item in the database" );
		}
		$this->data = array_pop( $this->data );
		$this->minimize();
		$this->changeExtsById( $ext );
	}

	private function minimize() {

		$storeKeys = array( 'id', 'name', 'price', 'discount', 'count', 'old_price', 'ext', 'category', 'description', 'visible', 'stock' );
		$tmpData = $this->data;
		$this->data = array();
		foreach( $tmpData as $k => $v ) {
			if( in_array( $k, $storeKeys ) ) {
				$this->data[$k] = $v;
			}
		}
	}

	public function updateExts() {

		$oldPrice = $this->price;
		$this->data = Catalog::getInstance()->getGoody( $this->id );
		if( empty( $this->data ) ) {
			throw new \Difra\Exception( "Can't find catalog item in the database" );
		}
		$this->data = array_shift( $this->data );
		$this->data['old_price'] = $oldPrice;
		$this->minimize();
		$this->_updatePrice();
	}

	public function getXML( $node ) {

		$node->setAttribute( 'id', $this->id );
		$node->setAttribute( 'count', $this->count );
		$node->setAttribute( 'price', $this->price );
		Catalog::getInstance()->_toXML( $node, array( $this->id => $this->data ) );
		if( !empty( $this->ext ) ) {
			$extNode = $node->appendChild( $node->ownerDocument->createElement( 'ext-data' ) );
			foreach( $this->ext as $k=>$v ) {
				$infoNode = $extNode->appendChild( $node->ownerDocument->createElement( 'info' ) );
				$infoNode->setAttribute( 'key', $k );
				$infoNode->setAttribute( 'value', $v );
			}
		}
	}

	public function changeExtsById( $data ) {
	
		$this->ext = array();
		if( !empty( $this->data['ext'] ) ) {
			foreach( $this->data['ext'] as $name => $inf ) {
				if( strpos( $inf['value'], '|' ) === false ) {
					continue;
				}
				$values = explode( '|', $inf['value'] );
				if( isset( $data[$inf['id']] ) ) {
					$this->ext[$name] = $data[$inf['id']];
				} else {
					$this->ext[$name] = $values[0]; // default value
				}
			}
		}
		$this->_updatePrice();
	}

	public function changeExtsByName( $data ) {
		
		$this->ext = array();
		if( !empty( $this->data['ext'] ) ) {
			foreach( $this->data['ext'] as $name => $inf ) {
				if( strpos( $inf['value'], '|' ) === false ) {
					continue;
				}
				$values = explode( '|', $inf['value'] );
				if( isset( $data[$name] ) and in_array( $data[$name], $values ) ) {
					$this->ext[$name] = $data[$name];
				} else {
					$this->ext[$name] = $values[0]; // default value
				}
			}
		}
		$this->_updatePrice();
	}

	private function _updatePrice() {
		
		$coef = 1;
		if( !empty( $this->data['ext'] ) ) {
			foreach( $this->data['ext'] as $name => $inf ) {
				if( strpos( $inf['value'], '|' ) === false ) {
					continue;
				}
				$values = explode( '|', $inf['value'] );
				$coefs = explode( '|', $inf['coef'] );
				if( !isset( $this->ext[$name] ) ) {
					$this->ext[$name] = $values[0];
				}
				if( sizeof( $coefs ) == sizeof( $values ) ) {
					$val = $this->ext[$name];
					$values = array_flip( $values );
					$finalValue = str_replace( ',', '.', $coefs[$values[$val]] );
					$coef += $finalValue - 1;
				}
			}
		}
		$this->price = $this->data['price'] * $coef;
		if( $this->data['discount'] ) {
			$this->price += $this->data['discount'] - $this->data['price'];
		}
		$this->price = ceil( $this->price );
	}
	
	// сравнение выбранных значений доп. характеристик (нужно для того, чтобы решить, добавлять новый элемент в корзину
	// или увеличить счётчик у уже существующего)
	public function compareExt( $data ) {
		
		if( empty( $this->ext ) and empty( $data ) ) {
			return true;
		}
		if( empty( $this->ext ) or empty( $data ) ) {
			return false;
		}
		foreach( $this->ext as $n => $v ) {
			if( !isset( $data[$n] ) or $data[$n] != $v ) {
				return false;
			}
			unset( $data[$n] );
		}
		if( !empty( $data ) ) {
			return false;
		}
		return true;
	}

}
*/