<?php

/*

/**
 * Класс корзины товаров
 * /
namespace Difra\Plugins\Catalog;

class Cart {
	
	public $list = array();
	private $changed = false;

	/**
	 * @static
	 * @return Cart
	 * /
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

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

	/**
	 * Загрузка данных корзины при инициализации
	 * @return void
	 * /
	public function _load() {

		if( !isset( $_SESSION['cart'] ) ) {
			$_SESSION['cart'] = array();
		}
		$this->list = $_SESSION['cart'];
		if( empty( $_SESSION['cart_logged'] ) and \Difra\Auth::getInstance()->logged ) {
			$_SESSION['cart_logged'] = true;
			$db = \Difra\MySQL::getInstance();
			$data = $db->fetch(
				'SELECT `cart` FROM `catalog_cart` WHERE `user`=\'' . $db->escape( \Difra\Auth::getInstance()->data['id'] )
				. "'" );
			if( !empty( $data ) ) {
				$data = @unserialize( $data[0]['cart'] );
				if( !empty( $data ) ) {
					foreach( $data as $item ) {
						$this->_add( $item );
					}
				}
			}
			$this->changed = true;
		}
		if( !empty( $_SESSION['cart_logged'] ) and !\Difra\Auth::getInstance()->logged ) {
			$_SESSION['cart_logged'] = '';
			$this->clear();
			$this->changed = true;
		}
	}

	/**
	 * Сохранение данных в корзине
	 * @return void
	 * /
	public function _save() {

		$_SESSION['cart'] = $this->list;
		if( \Difra\Auth::getInstance()->logged ) {
			$db = \Difra\MySQL::getInstance();
			$data = $db->escape( serialize( $this->list ) );
			$db->query( 'REPLACE LOW_PRIORITY INTO `catalog_cart` (`user`,`cart`) VALUES (\'' . $db->escape( \Difra\Auth::getInstance()->data['id'] ) . "','$data')" );
		}
	}

	/**
	 * Добавление товара в корзину
	 * @param int $id	ID товара
	 * @param array $ext	Дополнительные характеристики
	 * @return void
	 * /
	public function add( $id, $ext ) {
		
		$item = new \Difra\Plugins\Catalog\Cart\Item( $id, $ext );
		$this->_add( $item );
	}

	/**
	 * Добавление объекта товара в корзину
	 * @param Cart\Item $item
	 * @return bool
	 * /
	public function _add( $item ) {

		if( !empty( $this->list ) ) {
			foreach( $this->list as $v ) {
				if( $item->id != $v->id ) {
					continue;
				}
				if( $item->ext != $v->ext ) {
					continue;
				}
				$v->count++;
				return $this->changed = true;
			}
		}
		$this->list[] = $item;
		return $this->changed = true;
	}

	public function getList() {

		return $this->list;
	}

	public function getListXML( $node ) {

		if( empty( $this->list ) ) {
			return;
		}

		foreach( $this->list as $k => $item ) {
			$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			$itemNode->setAttribute( 'k', $k );
			$item->getXML( $itemNode );
		}
	}

	/**
	 * Возвращает общее количество предметов в корзине
	 * @return int
	 * /
	public function getCount() {

		if( empty( $this->list ) ) {
			return 0;
		}
		$c = 0;
		foreach( $this->list as $v ) {
			$c += $v->count;
		}
		return $c;
	}

	// TO DO: это нужно вынести в lang-файл, используя declension.xsl
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

	/**
	 * Очищает корзину
	 * @return void
	 * /
	public function clear() {

		$_SESSION['cart'] = $this->list = array();
		$this->changed = true;
	}

	/**
	 * Проверяет наличие товара в корзине по id
	 * @param int $id
	 * @return bool
	 * /
	public function isAdded( $id ) {

		foreach( $this->list as $item ) {
			if( $item->id == $id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Удаляет товар из корзины по id
	 * @param int $id
	 * @return bool
	 * /
	public function remove( $id ) {

		foreach( $this->list as $k=>$item ) {
			if( $item->id == $id ) {
				unset( $this->list[$k] );
				return $this->changed = true;
			}
		}
		return false;
	}

	/**
	 * Удаляет товар из корзины по номеру позиции
	 * @param int $key
	 * @return bool
	 * /
	public function removeByKey( $key ) {

		if( isset( $this->list[$key] ) ) {
			unset( $this->list[$key] );
			return $this->changed = true;
		}
		return false;
	}

	/**
	 * Возвращает ID всех товаров в корзине
	 * @return array
	 * /
	public function getIDs() {

		$ids = array();
		if( !empty( $this->list ) ) {
			foreach( $this->list as $item ) {
				$ids[] = $item->id;
			}
		}
		return array_unique( $ids );
	}

	/**
	 * Возвращает сумму
	 * @return float
	 * /
	public function getTotal() {

		if( empty( $this->list ) ) {
			return 0;
		}
		$total = 0;
		foreach( $this->list as $item ) {
			$total += $item->count * $item->price;
		}
		return $total;
	}

	/**
	 * Изменяет количество товаров в позиции
	 * @param int $k	номер позиции
	 * @param int $num	количество
	 * @return bool
	 * /
	public function setNum( $k, $num ) {

		$k = intval( (string)$k );
		if( !isset( $this->list[$k] ) ) {
			return false;
		}
		$this->list[$k]->count = intval( (string)$num );
		return $this->changed = true;
	}

}

*/