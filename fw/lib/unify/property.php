<?php

namespace Difra\Unify;

/**
 * Adapter к потомкам Type
 * Class Property
 *
 * @package Difra\Unify
 */
abstract class Property {

	//
	// Свойства для переопределения
	// (Нужно именно переопределять, а не записывать новые значения)
	//

	/** @var string Имя свойства */
	static protected $name = '';
	/** @var bool обязательное свойство, или (Not) Null */
	static protected $required = false;
	/** @var string Тип данных */
	static protected $type = '';
	/** @var int|null Длина поля (для int, char и т.д.) */
	static protected $length = null;
	/** @var mixed Значение по умолчанию */
	static protected $default = null;

	/** @var bool Индекс */
	static protected $index = false;
	static protected $unique = false;

	/** @var bool Primary key на этом поле */
	static protected $primary = false;

	/** @var bool Загружать сразу */
	static protected $autoload = true;
	/** @var bool Выполняется ли поиск по этому полю */
	static protected $search = true;

	//
	// Табличные данные
	//

	/** @var Object Родительский объект */
	protected $object = null;

	/** @var mixed Значение */
	protected $value = null;

	/** @var bool Флаг изменённого свойства */
	protected $modified = false;

	//
	// Устновка и получение значения
	//

	/**
	 * Получение значения
	 * @return mixed
	 */
	public function get() {

		if( !$this->value and self::$autoload ) {
			$this->load();
		}
		if( $this->value === null and $this->automatic ) {
			$this->autofill();
		}
		return $this->value;
	}

	/**
	 * Установка значения
	 * @param $value
	 * @return mixed
	 */
	public function set( $value ) {

		if( $this->value != $value ) {
			$this->value = $value;
			$this->modified = true;
			$this->object->setModified();
		}
	}

	/**
	 * Нефильтрованное значение
	 * @return null
	 */
	public function getRaw() {

		return $this->value;
	}

	// TODO: canonicalize

	//
	// Некоторые поля могут заполняться на основе значений других полей.
	// Пример: строка для url может генерироваться на основе id и имени.
	//

	/** @var bool Использовать this->autofill() для заполнения */
	protected $automatic = false;

	/**
	 * Автоматическое заполнение значения поля
	 * @return mixed
	 */
	abstract public function autofill();

	/**
	 * Определяет, было ли свойство изменено
	 * @return bool
	 */
	final public function isModified() {

		return $this->modified;
	}

	/**
	 * Возвращает имя свойства
	 * @return string
	 */
	final public function getName() {

		return self::$name;
	}

	final public function load() {
		// TODO
	}
}