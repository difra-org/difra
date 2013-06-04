<?php

namespace Difra\Unify;

use Difra\Exception;
use Difra\MySQL;
use Difra\Unify;

/**
 * Class Query
 *
 * @package Difra\Unify
 */
class Query {

	/** @var string Имя Unify-объекта, в котором искать */
	public $objKey = null;

	/** @var array Условия поиска */
	public $conditions = array();

	/** @var \Difra\Unify\Paginator Пагинатор */
	public $paginator = null;

	/** @var int Условие для LIMIT — с какого элемента выводить */
	public $limitFrom = null;
	/** @var int Условие для LIMIT — сколько элементов выводить */
	public $limitNum = null;

	/** @var string|string[] Условия сортировки */
	public $order = null;

	/** @var string[]|self[] Имена Unify-объектов или же Query, которые нужно приджойнить к запросу */
	private $with = array();

	/** @var bool Извлекать все столбцы, в том числе с autoload=false */
	public $full = false;

	/**
	 * Установить имя объектов для запроса
	 * @param $objKey
	 */
	public function setClass( $objKey ) {

		$this->objKey = $objKey;
	}

	/**
	 * Выполнение запроса
	 * @return \Difra\Unify[]|null
	 */
	public function doQuery() {

		$db = \Difra\MySQL::getInstance();
		$result = $db->fetch( $this->getQuery() );
		if( $this->paginator ) {
			$this->paginator->setTotal( $db->getFoundRows() );
		}
		if( empty( $result ) ) {
			return null;
		}
		$res = array();
		$class = Unify::getClass( $this->objKey );
		foreach( $result as $newData ) {
			$o = new $class;
			$o->data = $newData;
			$res[] = $o;
		}
		return $res;

	}

	/**
	 * Формирование строки запроса
	 * @return string
	 */
	public function getQuery() {

		$q = 'SELECT ';
		if( $this->paginator ) {
			$q .= 'SQL_CALC_FOUND_ROWS ';
		}

		$q .= $this->getSelectKeys();
		// TODO: JOIN keys (все джойны и т.п. надо выполнять в дочерних функциях, чтобы поддержать множественные джойны)
		$q .= " FROM `$table`";
		// TODO: ... LEFT JOIN ... ON ...
		$q .= $this->getWhere();
		$q .= $this->getOrder();
		$q .= $this->getLimit();

		return $q;
	}

	/**
	 * Формирование списка получаемых полей для запроса
	 * @return string
	 */
	public function getSelectKeys() {

		$db = \Difra\MySQL::getInstance();
		/** @var Unify $class */
		$class = Unify::getClass( $this->objKey );
		$keys = $class::getKeys( $this->full );
		$keys = $db->escape( $keys );
		$keysS = array();
		$table = $db->escape( $class::getTable() );
		foreach( $keys as $key ) {
			$keysS[] = "`$table`.`$key`";
		}
		return implode( ',', $keysS );
	}

	/**
	 * Получение части запроса с WHERE
	 *
	 * @return string
	 */
	public function getWhere() {

		$db = \Difra\MySQL::getInstance();
		/** @var Unify $class */
		$class = Unify::getClass( $this->objKey );
		$c = array();
		$defCond = $class::getDefaultSearchConditions();
		if( !empty( $defCond ) ) {
			foreach( $this->conditions as $k => $v ) {
				if( !is_numeric( $k ) ) {
					$c[] = '`' . $db->escape( $k ) . "`='" . $db->escape( $v ) . "'";
				} else {
					$c[] = $v;
				}
			}
		}
		if( !empty( $this->conditions ) ) {
			foreach( $this->conditions as $k => $v ) {
				if( !is_numeric( $k ) ) {
					$c[] = '`' . $db->escape( $k ) . "`='" . $db->escape( $v ) . "'";
				} else {
					$c[] = $v;
				}
			}
		}
		if( empty( $c ) ) {
			return '';
		}
		return ' WHERE ' . implode( ' AND ', $c );
	}

	/**
	 * Формирование строки ORDER для запроса
	 *
	 * @return string
	 */
	public function getOrder() {

		if( empty( $this->order ) ) {
			return '';
		}
		/** @var Unify $class */
		$class = Unify::getClass( $this->objKey );
		$table = $class::getTable();
		$o = MySQL::getInstance()->escape( $this->order );
		return ' ORDER BY `' . $table . '`.`' . implode( '`,`' . $table . '`.`', $o ) . '`';
	}

	/**
	 * Формирование строки LIMIT для запроса
	 *
	 * @return string
	 */
	public function getLimit() {

		if( $this->paginator ) {
			list( $this->limitFrom, $this->limitNum ) = $this->paginator->getLimit();
		}

		if( !$this->limitFrom and !$this->limitNum ) {
			return '';
		}
		$q = ' LIMIT ';
		$db = MySQL::getInstance();
		if( $this->limitFrom ) {
			$q .= "'" . $db->escape( $this->limitFrom ) . "',";
		}
		if( $this->limitNum ) {
			$q .= "'" . $db->escape( $this->limitNum ) . "'";
		} else {
			$q .= '999999'; // чтобы задать только отступ в LIMIT, считаем это отсутсвтием лимита :)
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

	/**
	 * Добавить имя объекта или Query, которые нужно приджойнить к запросу
	 *
	 * @param string|self $query
	 * @throws \Difra\Exception
	 */
	public function join( $query ) {

		if( is_string( $query ) ) {
			$q = new self;
			$q->setClass( $query );
			$this->with[] = $q;
		} elseif( $query instanceof Query ) {
			$this->with[] = $query;
		} else {
			throw new Exception( "Expected string or Unify\\Query as a parameter" );
		}
	}
}