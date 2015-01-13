<?php

namespace Difra\Unify;

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
	 *
	 * @return \Difra\Unify[]
	 */
	public function getList() {

		$result = $this->doQuery();
		if( empty( $result ) ) {
			return null;
		}
		foreach( $result as $k => $v ) {
			$primary = $v->getPrimaryValue();
			if( !isset( \Difra\Unify::$objects[$this->objKey] ) ) {
				\Difra\Unify::$objects[$this->objKey] = array();
			}
			if( isset( \Difra\Unify::$objects[$this->objKey][$primary] ) ) {
				$result[$k] = \Difra\Unify::$objects[$this->objKey][$primary];
			} else {
				$result[$k] = \Difra\Unify::$objects[$this->objKey][$primary] = $v;
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