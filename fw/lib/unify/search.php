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
class Search {

//	/** @var string[]|null По каким классам искать. Если null, то по всем. */
//	public $classes = null;
//	/** @var string[string] */
//	public $filters = array();
//	/** @var string */
//	public $text = null;

	/** @var Paginator Пагинатор */
	public $paginator = null;

	/** @var array Условия поиска */
	public $conditions = array();

	/** @var string Условия для LIMIT */
	public $limit = '';

	/** @var string|string[] Условия сортировки */
	public $order = null;

	/**
	 * Получение списка
	 * @param string $objKey
	 * @return mixed
	 */
	public function getList( $objKey ) {

		// Добавления условий поиска по умолчанию в начало списка
		$class = Unify::getClass( $objKey );
		$cond = $this->conditions;
		$this->conditions = array();
		$this->addConditions( $class::getDefaultSearchConditions() );
		$this->addConditions( $cond );
		// Добавление пагинатора
		if( $this->paginator ) {
			$this->limit = $this->paginator->getLimit();
		}
		// Поиск
		$result = $class::search( $this->getSearchString() );
		if( $this->paginator ) {
			$this->paginator->setTotal( MySQL::getInstance()->getFoundRows() );
		}
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

	/**
	 * Формирование строки для поиска
	 * @return string
	 */
	public function getSearchString() {

		$q = '';
		$db = MySQL::getInstance();
		if( !empty( $this->conditions ) ) {
			$c = array();
			foreach( $this->conditions as $k => $v ) {
				if( !is_numeric( $k ) ) {
					$c[] = '`' . $db->escape( $k ) . "`='" . $db->escape( $v ) . "'";
				}
			}
			if( !empty( $c ) ) {
				$q .= ' WHERE ' . implode( ' AND ', $c );
			}
		}
		if( $this->order ) {
			$o = $db->escape( $this->order );
			$q .= ' ORDER BY ' . implode( ',', $o );
		}
		if( $this->limit ) {
			$q .= ' LIMIT ' . $db->escape( $this->limit );
		}
		return $q;
	}

	/**
	 * Добавить условие поиска
	 * В формате ключ = значение или строка.
	 * В строке можно передавать более сложные условия, но тогда должна быть подготовлена (MySQL->escape и т.п.)
	 *
	 * @param string|array $conditions
	 */
	public function addConditions( $conditions ) {

		if( !$conditions or empty( $conditions ) ) {
			return;
		}
		if( !is_array( $conditions ) ) {
			$this->conditions[] = $conditions;
			return;
		}
		foreach( $conditions as $k => $cond ) {
			if( !is_numeric( $k ) ) {
				$this->conditions[$k] = $cond;
			} else {
				$this->conditions[] = $cond;
			}
		}
	}

	/**
	 * Установить пагинатор для использования при выводе результатов
	 * @param Paginator $paginator
	 * @throws \Difra\Exception
	 */
	public function setPaginator( $paginator ) {

		if( !( $paginator instanceof Paginator ) ) {
			throw new Exception( "Expected Unify\\Paginator instance as a parameter" );
		}
		$this->paginator = $paginator;
	}
}