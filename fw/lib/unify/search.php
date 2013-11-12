<?php

namespace Difra\Unify;

use Difra\Exception;
use Difra\MySQL;
use Difra\Unify;

/**
 * Поиск
 * Class Search
 *
 * @package Difra\Unify
 */
class Search extends Query {

//	/** @var string[]|null По каким классам искать. Если null, то по всем. */
//	public $classes = null;
//	/** @var string[string] */
//	public $filters = array();
//	/** @var string */
//	public $text = null;

	/**
	 * Получение списка
	 * @return \Difra\Unify[]
	 */
	public function getList() {

		$result = $this->doQuery();
		if( empty( $result ) ) {
			return null;
		}
		foreach( $result as $k => $v ) {
			$primary = $v->getPrimaryValue();
			if( !isset( Unify::$objects[$this->objKey] ) ) {
				Unify::$objects[$this->objKey] = array();
			}
			if( isset( Unify::$objects[$this->objKey][$primary] ) ) {
				$result[$k] = Unify::$objects[$this->objKey][$primary];
			} else {
				$result[$k] = Unify::$objects[$this->objKey][$primary] = $v;
			}
		}
		return $result;
	}

	/**
	 * Добавление в XML полученного списка
	 *
	 * @param \DOMNode $toNode
	 */
	public function getListXML( $toNode ) {

		/** @var \DOMElement $node */
		$node = $toNode->appendChild( $toNode->ownerDocument->createElement( $this->objKey . 'List' ) );
		$list = $this->getList();
		if( empty( $list ) ) {
			$node->setAttribute( 'empty', 1 );
		} else {
			foreach( $list as $item ) {
				$itemNode = $node->appendChild( $toNode->ownerDocument->createElement( $this->objKey ) );
				/** @var $item \Difra\Unify */
				$item->getXML( $itemNode );
			}
		}
		if( $this->page ) {
			$this->getPaginatorXML( $node );
		}
	}
}