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
	 * @param string $objKey
	 * @return mixed
	 */
	public function getList( $objKey ) {

		$this->objKey = $objKey;
		$result = $this->doQuery();
		return $result;
	}

	/**
	 * Добавление в XML полученного списка
	 *
	 * @param string   $objKey
	 * @param \DOMNode $toNode
	 */
	public function getListXML( $objKey, $toNode ) {

		/** @var \DOMElement $node */
		$node = $toNode->appendChild( $toNode->ownerDocument->createElement( $objKey . 'List' ) );
		$list = $this->getList( $objKey );
		if( empty( $list ) ) {
			$node->setAttribute( 'empty', 1 );
		} else {
			foreach( $list as $item ) {
				$itemNode = $node->appendChild( $toNode->ownerDocument->createElement( $objKey ) );
				$item->getXML( $itemNode );
			}
		}
		if( $this->paginator ) {
			$this->paginator->getXML( $node );
		}
	}
}