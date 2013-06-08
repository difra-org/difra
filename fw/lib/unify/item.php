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
	 * Деструктор
	 */
	public function __destruct() {

		$this->save();
	}

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
		$self = get_called_class();
		/** @var array $propertiesList */
		if( !isset( $self::$propertiesList[$name] ) ) {
			/** @var $objKey string */
			throw new Exception( "Object '{$self::$objKey}' has no property '$name'." );
		}
		$this->load( isset( $self::$propertiesList[$name]['autoload'] ) ? !$self::$propertiesList[$name]['autoload'] : false );
		return $this->data[$name];
	}

	/**
	 * Установка значения поля
	 * @param string $name
	 * @param mixed  $value
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

		$this->loadByField( $this::getPrimary(), $this->getPrimaryValue(), $full );
	}

	/**
	 * Получить элемент по значению определённого поля
	 * @param string $field
	 * @param mixed  $value
	 * @param bool   $full
	 * @throws \Difra\Exception
	 */
	public function loadByField( $field, $value, $full = false ) {

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
			'SELECT `' . implode( '`,`', $db->escape( $this::getKeys( $full ) ) ) . '` FROM `' . $db->escape( $this::getTable() ) . '`'
			. ' WHERE `' . $db->escape( $field ) . "`='" . $db->escape( $value ) . "'"
		);
		if( empty( $data ) ) {
			/** @var $self self */
			/** @var $objKey string */
			throw new Exception( "No such object: '" . $self::$objKey . "' with `" . $field . "`='" . $value . "'." );
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

		$where = array();
		$db = MySQL::getInstance();
		if( $primary = $this->getPrimaryValue() ) {
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
			$this->full = true;
			$this->tempPrimary = $db->getLastId();
			$self = get_called_class();
			/** @var $objKey string */
			self::$objects[$self::$objKey][$this->tempPrimary] = $this;
		}
		$this->modified = array();
	}

	/** @var string[string $objKey][bool $full][] Список ключей для загрузки */
	protected static $objKeys = array();

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
//			// Пропускаем внешние ключи
//			if( $prop == 'foreign' or ( isset( $prop['type'] ) and $prop['type'] == 'foreign' ) ) {
//				continue;
//			}
			// Пропускаем составные индексы
			if( isset( $prop['type'] ) and $prop['type'] == 'index' ) {
				continue;
			}
			// При не полной загрузке пропускаем поля с autoload=false
			if( !$full and isset( $prop['autoload'] ) and !$prop['autoload'] ) {
				continue;
			}
			// При загрузке только полей с autoload=false пропускаем поля без этого свойства
			if( $full === 'only' and ( !isset( $prop['autoload'] ) or $prop['autoload'] ) ) {
				continue;
			}
			$self::$objKeys[$self::$objKey][$full][] = $name;
		}
		return $self::$objKeys[$self::$objKey][$full];
	}

	/**
	 * Добавление данных в XML-ноду
	 *
	 * @param \DOMNode|\DOMElement $node
	 */
	public function getXML( $node ) {

		$this->load();
		if( empty( $this->data ) ) {
			return;
		}
		foreach( $this->data as $k => $v ) {
			$node->setAttribute( $k, $v );
		}
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
	 * Возвращает критерии поиска по умолчанию
	 * @return array|null
	 */
	public static function getDefaultSearchConditions() {

		$self = get_called_class();
		/** @var $defaultSearch null|array */
		return $self::$defaultSearch();
	}

	/**
	 * Создание нового объекта
	 * @return self
	 */
	public static function create() {

		$self = get_called_class();
		return new $self;
	}

	/**
	 * Возвращает объект с заданным primary
	 *
	 * @param $primary
	 */
	public static function get( $primary ) {

		$self = get_called_class();
		/** @var string $objKey */
		$objKey = $self::$objKey;
		if( isset( self::$objects[$objKey][$primary] ) ) {
			return self::$objects[$objKey][$primary];
		}
		$o = new $self;
		$o->tempPrimary = $primary;
		if( !isset( self::$objects[$objKey] ) ) {
			self::$objects[$objKey] = array();
		}
		self::$objects[$objKey][$primary] = $o;
		return $o;
	}

	/**
	 * Возвращает объект по значению поля (если соответствующих строк в таблице несколько, будет возвращён только первый)
	 * @param string $field
	 * @param string $value
	 * @return self
	 */
	public static function getByField( $field, $value ) {

		$self = get_called_class();
		/** @var string $objKey */
		$objKey = $self::$objKey;
		/** @var $o self */
		$o = new $self;
		$o->loadByField( $field, $value );
		if( $primary = $o->getPrimaryValue() ) {
			if( !isset( self::$objects[$objKey] ) ) {
				self::$objects[$objKey] = array();
			}
			if( !isset( self::$objects[$objKey][$primary] ) ) {
				return self::$objects[$objKey][$primary] = $o;
			} else {
				// такой объект уже есть — вернём его, а полученный оставим сборщику мусора
				return self::$objects[$objKey][$primary];
			}
		}
		return $o;
	}
}