<?php

namespace Difra\Unify;

use Difra\MySQL;

/**
 * Class Item
 *
 * @package Difra\Unify
 */
abstract class Item extends Storage {

	/**
	 * TODO: рассмотреть необходимость добавления свойств и соответствующих методов. Вероятно, это нужно добавлять в Query, но тогда тут должна быть какая-то связка
	 * Unify::parents[$name] - ???
	 * Unify::children[$name] - ???
	 */

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
	 *
	 * @return mixed
	 * @throws \Difra\Exception
	 */
	public function __get( $name ) {

		if( isset( $this->data[$name] ) ) {
			return $this->data[$name];
		}
		if( !isset( static::$propertiesList[$name] ) ) {
			throw new \Difra\Exception( "Object '" . static::getObjKey() . "' has no property '$name'." );
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
	 */
	public function load( $full = false ) {

		$this->loadByField( static::getPrimary(), $this->getPrimaryValue(), $full );
	}

	/**
	 * Загрузить элемент по значению определённого поля (для getByField())
	 *
	 * @param string $field
	 * @param mixed  $value
	 * @param bool   $full
	 *
	 * @throws \Difra\Exception
	 */
	protected function loadByField( $field, $value, $full = false ) {

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
			'SELECT `' . implode( '`,`', $db->escape( static::getKeys( $full ) ) ) . '` FROM `' . $db->escape( static::getTable() ) . '`'
			. ' WHERE `' . $db->escape( $field ) . "`='" . $db->escape( $value ) . "'"
		);
		if( empty( $data ) ) {
			throw new \Difra\Exception( "No such object: '" . static::getObjKey() . "' with `" . $field . "`='" . $value . "'." );
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
			self::$objects[static::getObjKey()][$this->tempPrimary] = $this;
		}
		$this->modified = array();
	}

	/** @var string[string $objKey][bool $full][] Список ключей для загрузки */
	protected static $objKeys = array();

	/**
	 * Возвращает список ключей (обёртка для getKeysArray)
	 *
	 * @param bool $full|'only'        Вместе с ключами с autoload=false
	 *
	 * @return array
	 */
	public static function getKeys( $full = true ) {

		$objKey = static::getObjKey();
		if( isset( static::$objKeys[$objKey][$full] ) ) {
			return static::$objKeys[$objKey][$full];
		}
		if( !isset( static::$objKeys[$objKey] ) ) {
			static::$objKeys[$objKey] = array();
		}
		return static::$objKeys[$objKey][$full] = static::getKeysArray( $full );
	}

	/**
	 * Возвращает список ключей
	 * @param bool $full|'only'        Вместе с ключами с autoload=false
	 *
	 * @return array
	 */
	private static function getKeysArray( $full = true ) {

		$keys = array();
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
			$keys[] = $name;
		}
		return $keys;
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

	private static function getClassParts() {

		static $parts = null;
		if( !is_null( $parts ) ) {
			return $parts;
		}
		$parts = explode( '\\', $class = get_called_class() );
		if( sizeof( $parts ) < 4 or $parts[0] != 'Difra' or $parts[1] != 'Plugins' or $parts[3] != 'Objects' ) {
			throw new \Difra\Exception( 'Bad object class name: ' . $class );
		}
		unset( $parts[3] );
		unset( $parts[1] );
		unset( $parts[0] );
		return $parts;
	}

	/**
	 * Возвращает имя таблицы
	 * @return string
	 */
	public static function getTable() {

		static $table = null;
		if( !is_null( $table ) ) {
			return $table;
		}
		return $table = mb_strtolower( implode( '_', static::getClassParts() ) );
	}

	/**
	 * Возвращает имя объекта
	 * @return string
	 */
	public static function getObjKey() {

		static $objKey = null;
		if( !is_null( $objKey ) ) {
			return $objKey;
		}
		return $objKey = implode( static::getClassParts() );
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

		return isset( $this->data[$pri = static::getPrimary()] ) ? $this->data[$pri] : $this->tempPrimary;
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
	 *
	 * @return static
	 */
	public static function get( $primary ) {

		$objKey = static::getObjKey();
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
	 *
	 * @return static
	 */
	public static function getByField( $field, $value ) {

		$objKey = static::getObjKey();
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

	/**
	 * Получение статуса таблицы объекта
	 * @return array
	 */
	public static function getObjDbStatus() {

		$table = static::getTable();
		$db = MySQL::getInstance();
		try {
			$db->fetch( "DESC `" . $db->escape( $table ) . "`" );
		} catch( \Difra\Exception $ex ) {
			return array( 'status' => 'missing', 'name' => $table );
		}
		// TODO: таблицы отличаются?
		return array( 'status' => 'ok' );
	}

	/**
	 * Получение статуса таблицы объекта в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public static function getObjDbStatusXML( $node ) {

		$status = self::getObjDbStatus();
		foreach( $status as $ak => $av ) {
			$node->setAttribute( $ak, $av );
		}
	}

	/**
	 * Получение строки для создания таблицы
	 * @throws \Difra\Exception
	 * @return string
	 */
	public static function getDbCreate() {

		if( empty( static::$propertiesList ) ) {
			throw new \Difra\Exception( 'Can\'t create table for empty object.' );
		}
		$db = MySQL::getInstance();
		$columns = array();
		$indexes = array();
		foreach( static::$propertiesList as $name => $prop ) {
			// simple columns (name => type)
			if( !is_array( $prop ) ) {
				$columns[] = '  `' . $db->escape( $name ) . '` ' . $prop;
				continue;
			}
			// column name
			$line = '  `' . $db->escape( $name ) . '` ' . $prop['type'];
			// primary key
			$primary = ( !empty( $prop['primary'] ) and $prop['primary'] );
			if( $primary ) {
				$indexes[] = ' PRIMARY KEY (`' . $name . '`)';
			}
			// length
			empty( $prop['length'] ) ? : $line .= "({$prop['length']})";
			// default value
			if( !empty( $prop['default'] ) ) {
				$line .= " DEFAULT {$prop['default']}";
			} elseif( !empty( $prop['required'] ) and $prop['required'] ) {
				$line .= ' NOT NULL';
			} else {
				$line .= ' DEFAULT NULL';
			}
			// column options
			empty( $prop['options'] ) ? :
				$line .= mb_strtoupper( ' ' . ( is_array( $prop['options'] ) ? implode( ' ', $prop['options'] ) : $prop['options'] ) );
			// non-primary indexes
			if( !$primary and !empty( $prop['unique'] ) and $prop['unique'] ) {
				$indexes[] = '  UNIQUE KEY `' . $name . '` (`' . $name . '`)';
			} elseif( !$primary and !empty( $prop['index'] ) and $prop['index'] ) {
				$indexes[] = '  KEY `' . $name . '` (`' . $name . '`)';
			}

			$columns[] = $line;
		}
		$lines = array_merge( $columns, $indexes );
		$create = 'CREATE TABLE `' . static::getTable() . "` (\n" . implode( ",\n", $lines ) . "\n)";
		return $create;
	}

	/**
	 * Создание таблицы для объекта
	 */
	public static function createDb() {

		MySQL::getInstance()->query( self::getDbCreate() );
	}
}