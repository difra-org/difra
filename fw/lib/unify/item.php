<?php

namespace Difra\Unify;

use Difra\Exception, Difra\MySQL;

/**
 * Class Item
 *
 * @package Difra\Unify
 */
abstract class Item extends Storage {

	/** TODO
	 * Unify::parents[$name] - ???
	 * Unify::children[$name] - ???
	 * Unify::objects[$name][$id] — список объектов - ???
	 * Обновить wiki
	 */

	/** @var string Имя класса (post, comment, user, etc.) */
	static protected $objKey = null;
	/** @var Имя таблицы */
	static protected $table = null;
	/** @var array[string $name] */
	static protected $propertiesList = null;
	/** @var Имя Property с Primary Key */
	static protected $primary = null;

	/** @var null|array Дефолтные условия поиска */
	static protected $defaultSearch = null;

	/**
	 * Работа с объектом
	 */

	protected $data = null; // данные
	protected $full = false; // данные загружены полностью
	protected $modified = array();
	protected $tempPrimary = null;

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
		if( !isset( static::$propertiesList[$name] ) ) {
			throw new Exception( "Object '" . static::$objKey . "' has no property '$name'." );
		}
		$this->load( isset( static::$propertiesList[$name]['autoload'] ) ? !static::$propertiesList[$name]['autoload'] : false );
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
			throw new Exception( "No such object: '" . static::$objKey . "' with `" . $field . "`='" . $value . "'." );
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
			/** @var $objKey string */
			self::$objects[static::$objKey][$this->tempPrimary] = $this;
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

		if( isset( static::$objKeys[static::$objKey][$full] ) ) {
			return static::$objKeys[static::$objKey][$full];
		}
		if( !isset( static::$objKeys[static::$objKey] ) ) {
			static::$objKeys[static::$objKey] = array();
		}
		static::$objKeys[static::$objKey][$full] = array();
		foreach( static::$propertiesList as $name => $prop ) {
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
			static::$objKeys[static::$objKey][$full][] = $name;
		}
		return static::$objKeys[static::$objKey][$full];
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

		return static::$table;
	}

	/**
	 * Возвращает имя столбца с primary key
	 *
	 * @return string
	 */
	public static function getPrimary() {

		return static::$primary;
	}

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

		return static::$defaultSearch;
	}

	/**
	 * Создание нового объекта
	 * @return static
	 */
	public static function create() {

		return new static;
	}

	/**
	 * Возвращает объект с заданным primary
	 *
	 * @param $primary
	 * @return static
	 */
	public static function get( $primary ) {

		$objKey = static::$objKey;
		if( isset( self::$objects[$objKey][$primary] ) ) {
			return self::$objects[$objKey][$primary];
		}
		$o = new static;
		/** @var $o self */
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
	 * @return static
	 */
	public static function getByField( $field, $value ) {

		$objKey = static::$objKey;
		$o = new static;
		/** @var $o self */
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