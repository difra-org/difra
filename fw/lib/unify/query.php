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
class Query extends Paginator {

	/** @var string Имя Unify-объекта, в котором искать */
	public $objKey = null;

	/** @var array Условия поиска */
	public $conditions = array();

	/** @var int Условие для LIMIT — с какого элемента выводить */
	public $limitFrom = null;
	/** @var int Условие для LIMIT — сколько элементов выводить */
	public $limitNum = null;

	/** @var string|string[] Условия сортировки */
	private $order = null;
	private $orderDesc = array();

	/** @var string[]|self[] Имена Unify-объектов или же Query, которые нужно приджойнить к запросу */
	private $with = array();

	/** @var bool Извлекать все столбцы, в том числе с autoload=false */
	public $full = false;

	/**
	 * Конструктор
	 * @param $objKey Имя объектов для запроса
	 *
	 * @throws \Difra\Exception
	 */
	public function __construct( $objKey ) {

		parent::__construct();
		$this->objKey = $objKey;
		if( !$class = Storage::getClass( $objKey ) ) {
			throw new Exception( 'objKey \'' . $objKey . '\' does not exist.' );
		}
		$this->order = $class::getDefaultOrder() ? (array)$class::getDefaultOrder() : null;
		$this->orderDesc = $class::getDefaultOrderDesc() ? (array)$class::getDefaultOrderDesc() : null;
	}

	/**
	 * Выполнение запроса
	 * @return \Difra\Unify[]|null
	 */
	public function doQuery() {

//		try {
		$db = MySQL::getInstance();
		$result = $db->fetch( $this->getQuery() );
//		} catch( Exception $ex ) {
//			return null;
//		}
		if( $this->page ) {
			$this->setTotal( $db->getFoundRows() );
		}
		if( empty( $result ) ) {
			return null;
		}
		$res = array();
		$class = Unify::getClass( $this->objKey );
		foreach( $result as $newData ) {
			/** @var Item $o */
			$o = new $class;
			$o->setData( $newData );
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
		if( $this->page ) {
			$q .= 'SQL_CALC_FOUND_ROWS ';
		}

		$q .= $this->getSelectKeys();
		// TODO: JOIN keys (все джойны и т.п. надо выполнять в дочерних функциях, чтобы поддержать множественные джойны)
		$class = Unify::getClass( $this->objKey );
		/** @var $class Item */
		$q .= " FROM `{$class::getTable()}`";
		// TODO: ... LEFT JOIN ... ON ...
		$q .= $this->getWhere();
		$q .= $this->getOrder();
		$q .= $this->getLimit();

		return $q;
	}

	/**
	 * Формирование списка получаемых полей для запроса
	 * @throws \Difra\Exception
	 * @return string
	 */
	public function getSelectKeys() {

		$db = MySQL::getInstance();
		/** @var Unify $class */
		$class = Unify::getClass( $this->objKey );
		if( !$class ) {
			throw new Exception( "Can't query unknown object '{$this->objKey}'" );
		}
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

		$db = MySQL::getInstance();
		/** @var Unify $class */
		$class = Unify::getClass( $this->objKey );
		$conditions = !empty( $this->conditions ) ? $this->conditions : $class::getDefaultSearchConditions();
		if( empty( $conditions ) ) {
			return '';
		}
		$c = array();
		foreach( $conditions as $k => $v ) {
			if( !is_numeric( $k ) ) {
				$c[] = '`' . $db->escape( $k ) . "`='" . $db->escape( $v ) . "'";
			} else {
				$c[] = $v;
			}
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
		$db = MySQL::getInstance();
		$table = $db->escape( $class::getTable() );
		$ord = ' ORDER BY ';
		$d = '';
		foreach( (array)$this->order as $column ) {
			$ord .= "$d`$table`.`" . $db->escape( $column ) . '`' .
				( ( empty( $this->orderDesc ) or !in_array( $column, $this->orderDesc ) ) ? '' : ' DESC' );
			$d = ', ';
		}
		return $ord;
	}

	public function setOrder( $columns = array(), $desc = array() ) {

		if( !$columns or empty( $columns ) ) {
			$this->order = null;
			$this->orderDesc = null;
		}
		$this->order = is_array( $columns ) ? $columns : array( $columns );
		$this->orderDesc = is_array( $desc ) ? $desc : array( $desc );
	}

	/**
	 * Формирование строки LIMIT для запроса
	 *
	 * @return string
	 */
	public function getLimit() {

		if( $this->page ) {
			list( $this->limitFrom, $this->limitNum ) = $this->getPaginatorLimit();
		}

		if( !$this->limitFrom and !$this->limitNum ) {
			return '';
		}
		$q = ' LIMIT ';
		if( $this->limitFrom ) {
			$q .= intval( $this->limitFrom ) . ",";
		}
		if( $this->limitNum ) {
			$q .= intval( $this->limitNum );
		} else {
			$q .= '999999'; // чтобы задать только отступ в LIMIT, считаем это отсутсвтием лимита :)
		}
		return $q;
	}

	/**
	 * Добавить условие поиска
	 * В формате ключ = значение.
	 * @param array $conditions
	 *
	 * @throws \Difra\Exception
	 */
	public function addConditions( $conditions ) {

		if( !is_array( $conditions ) ) {
			throw new Exception( 'Difra\Unify\Query->addConditions() accepts only array as parameter.' );
		}
		if( empty( $conditions ) ) {
			return;
		}
		foreach( $conditions as $k => $v ) {
			$this->addCondition( $k, $v );
		}
	}

	/**
	 * Добавить условие поиска
	 * @param string $column
	 * @param string $value
	 *
	 * @throws \Difra\Exception
	 */
	public function addCondition( $column, $value ) {

		$this->conditions[$column] = $value;
	}

	/**
	 * Добавить условие поиска
	 * В строке можно передавать более сложные условия, чем в addConditions() но тогда она должна быть подготовлена (MySQL->escape и т.п.).
	 *
	 * @param string|string[] $condition
	 */
	public function addCustomConditions( $condition ) {

		if( !is_array( $condition ) ) {
			$this->conditions[] = $condition;
		} else {
			foreach( $condition as $cond ) {
				$this->addCustomConditions( $cond );
			}
		}
	}

	/**
	 * Добавить имя объекта или Query, которые нужно приджойнить к запросу
	 *
	 * @param string|self $query
	 *
	 * @throws \Difra\Exception
	 */
	public function join( $query ) {

		if( is_string( $query ) ) {
			$q = new self( $query );
			$this->with[] = $q;
		} elseif( $query instanceof Query ) {
			$this->with[] = $query;
		} else {
			throw new Exception( "Expected string or Unify\\Query as a parameter" );
		}
	}
}