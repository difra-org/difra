<?php

namespace Difra\Unify;

use Difra\Exception, Difra\MySQL;

/**
 * Class Item
 *
 * @package Difra\Unify
 */
class Item extends Storage {
	/**
	 * Работа с объектом
	 *
	 */

	protected $data = null; // данные
	protected $full = false; // данные загружены полностью
	protected $modified = array();

	/**
	 * Получение значения поля
	 * @param $name
	 * @return mixed
	 * @throws Exception
	 */
	public function __get( $name ) {

		if( isset( $this->data[$name] ) ) {
			return $this->data[$name];
		}
		/** @var array $propertiesList */
		if( !isset( $this::$propertiesList[$name] ) ) {
			/** @var $objKey string */
			throw new Exception( "Object '{$this::$objKey}' has no property '$name'." );
		}
		$this->load( isset( $this::$propertiesList[$name]['autoload'] ) ? !$this::$propertiesList[$name]['autoload'] : false );
		return $this->data[$name];
	}

	/**
	 * Установка значения поля
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ) {

		if( $this->$name === $value ) {
			return;
		}
		$this->data[$name] = $value;
		$this->modified[$name] = $value;
	}

	/**
	 * Загружает данные
	 * @param bool $full        Включать ли поля с autoload=false
	 * @throws Exception
	 */
	public function load( $full = false ) {

		if( $this->full ) {
			return;
		}
		if( !is_null( $this->data ) ) {
			if( !$full ) {
				return;
			} else {
				$full = 'only';
			}
		}
		$db = MySQL::getInstance();
		$data = $db->fetchRow(
			'SELECT `' . implode( '`,`', $db->escape( $this->getKeys( $full ) ) ) . '` FROM `' . $db->escape( $this::getTable() ) . '`'
			. ' WHERE `' . $db->escape( $this::getPrimary() ) . "`='" . $db->escape( $this->getPrimaryValue() ) . "'"
		);
		if( empty( $data ) ) {
			throw new Exception( "No such object: '" . $this::getObjKey() . "' with `" . $this->getPrimary() . "`='" . $this->getPrimaryValue() . "'." );
		}
		$this->full = $full ? true : false;
		if( is_null( $this->data ) ) {
			$this->data = $data;
		} else {
			foreach( $data as $k => $v ) {
				$this->data[$k] = $v;
			}
		}
	}

	/**
	 * Сохранение изменений
	 */
	public function save() {

		$primary = $this->getPrimaryValue();
		$where = array();
		$db = MySQL::getInstance();
		if( $primary ) {
			if( empty( $this->modified ) ) {
				return;
			}
			$query = 'UPDATE `' . $db->escape( $this->getTable() ) . '`';
			$where[] = '`' . $db->escape( $primary ) . "`='" . $db->escape( $this->getPrimaryValue() ) . "'";
		} else {
			$query = 'INSERT INTO `' . $this->getTable() . '`';
		}
		$mod = $db->escape( $this->modified );
		$set = array();
		foreach( $mod as $name => $property ) {
			$set[] = "`$name`='$property'";
		}
		if( !empty( $set ) ) {
			$query .= ' SET ' . implode( ',', $set );
		}
		if( !empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}
		$db->query( $query );
		if( !$primary ) {
			$this->tempPrimary = $db->getLastId();
		}
		$this->modified = array();
	}

	/** @var string[string $objKey][bool $full][] Список ключей для обычной загрузки */
	protected $objKeys = array();

	/**
	 * Возвращает список ключей
	 * @param bool $full|'only'        Вместе с ключами с autoload=false
	 * @return array
	 */
	public static function getKeys( $full = true ) {

		$self = get_called_class();
		/** @var $objKeys array */
		/** @var $objKey string */
		if( isset( $self::$objKeys[$self::$objKey][$full] ) ) {
			return $self::$objKeys[$self::$objKey][$full];
		}
		if( !isset( $self::$objKeys[$self::$objKey] ) ) {
			$self::$objKeys[$self::$objKey] = array();
		}
		$self::$objKeys[$self::$objKey][$full] = array();
		/** @var $propertiesList array */
		foreach( $self::$propertiesList as $name => $prop ) {
			if( !$full and isset( $prop['$autoload'] ) and !$prop['autoload'] ) {
				continue;
			}
			if( $full === 'only' and isset( $prop['$autoload'] ) and !$prop['autoload'] ) {
				continue;
			}
			$self::$objKeys[$self::$objKey][$full][] = $name;
		}
		return $self::$objKeys[$self::$objKey][$full];
	}

	/**
	 * Возвращает имя таблицы
	 * @return string
	 */
	public static function getTable() {

		$self = get_called_class();
		/** @var string $table */
		return $self::$table;
	}

	/**
	 * Возвращает имя столбца с primary key
	 *
	 * @return string
	 */
	public static function getPrimary() {

		$self = get_called_class();
		/** @var string $primary */
		return $self::$primary;
	}

	/**
	 * @var mixed Хранилище для значения primary key не загруженных объектов
	 */
	protected $tempPrimary = null;

	/**
	 * Возвращает значение столбца с primary key
	 *
	 * @return mixed|null
	 */
	public function getPrimaryValue() {

		return isset( $this->data[$pri = $this::getPrimary()] ) ? $this->data[$pri] : $this->tempPrimary;
	}

	/**
	 * Возвращает объект с заданным primary
	 *
	 * @param $primary
	 */
	public static function get( $primary ) {

		$self = get_called_class();
		$o = new $self;
		$o->tempPrimary = $primary;
	}

}