<?php

namespace Difra\Unify;

use Difra\MySQL;

// TODO: проверка заполненности обязательных свойств

/**
 * Абстрактный класс для объектов БД
 * Class object
 *
 * @package Difra\Unify
 */
abstract class Object {

	//
	// Табличные свойства
	//

	/** @var Property[string $name] */
	protected $properties = array();

	/** @var Имя Property с Primary Key */
	static protected $primary = null;

	/** @var Index[] Сложные индексы */
	static protected $indexes = array();

	/** @var array Опции ('Name'=>'table_name','engine'=>'InnoDB',etc.) */
	static protected $options = array();

	/** @var Object[string][] Объекты родителей в формате Object[], они же внешние ключи */
	protected $children = array();

	// /** @var Rows[] Предзаданные строки */
	// protected $rows = array();

	// Внутренние переменные

	/** @var bool Флаг изменений */
	protected $modified = false;
	/** @var bool Флаг загруженного объекта */
	protected $loaded = false;

	//
	// Методы для переопределения
	//

	/**
	 * Генерация строки для ссылки на объект
	 * @return mixed
	 */
	public function getLink() {

		return $this->getPrimary()->get();
	}

	//
	// Реализация основного функционала
	//

	/**
	 * Загрузка данных объекта из базы
	 */
	public function load() {

		if( $this->loaded ) {
			return;
		}
		$db = MySQL::getInstance();
		$primary = $this->getPrimary();
		$data =
			$db->fetch( 'SELECT * FROM `' . $db->escape( $this->getTableName() ) . ' WHERE `' . $primary->getName() . "`='" . $db->escape( $primary->get() ) .
				"'" );
		$this->loadData( $data );
	}

	/**
	 * Загрузка данных объекта из массива значений
	 * @param $data
	 */
	public function loadData( $data ) {

		foreach( $this->properties as $name => $property ) {
			if( isset( $data[$name] ) ) {
				$property->set( $data[$name] );
			}
		}
		$this->loaded = true;
	}

	/**
	 * Сохранение изменений
	 */
	final public function save() {

		if( !$this->modified ) {
			return;
		}
		$primary = $this->getPrimary();
		$where = array();
		$db = MySQL::getInstance();
		if( $primary->get() ) {
			$query = 'UPDATE `' . $this->getTableName() . '`';
			$where[] = '`' . $db->escape( $primary->getName() ) . "`='" . $db->escape( $primary->get() ) . "'";
		} else {
			$query = 'INSERT INTO `' . $this->getTableName() . '`';
		}
		$modified = $this->getModifiedProperties();
		$set = array();
		foreach( $modified as $name => $property ) {
			$set[] = '`' . $db->escape( $name ) . "`='" . $db->escape( $property->get() ) . "'";
		}
		if( !empty( $set ) ) {
			$query .= ' SET ' . implode( ',', $set );
		}
		if( !empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}
		$db->query( $query );
		$this->modified = false;
	}

	final public function __destruct() {

		$this->save();
	}

	/**
	 * Возвращает список изменённых Property
	 *
	 * @return Property[]|bool
	 */
	final public function getModifiedProperties() {

		$modified = array();
		foreach( $this->properties as $name => $property ) {
			if( $property->isModified() ) {
				$modified[$name] = $property;
			}
		}
		return empty( $modified ) ? false : $modified;
	}

	/**
	 * Устанавливает флаг изменений
	 * @param bool $modified
	 */
	final public function setModified( $modified = true ) {

		$this->modified = $modified;
	}

	/**
	 * Возвращает Property, соответствующее Primary Key
	 *
	 * @return Property
	 */
	final protected function getPrimary() {

		return $this->properties[self::$primary];
	}

	/**
	 * Возвращает имя таблицы
	 * @return string
	 */
	final protected function getTableName() {

		return self::$options['name'];
	}
}
