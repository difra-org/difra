<?php

namespace Difra\MySQL\SQL;

/**
 * Class Common
 *
 * @package Difra\MySQL\SQL
 * @deprecated
 */
abstract class Common {

	/** @var self[] Если объекты в базе именованные (например, таблицы), то добавляем их в этот массив в виде: имя => объект */
	protected static $list = array();

	/**
	 * Создание объекта из чанков
	 * @param array $chunks
	 * @return mixed
	 */
	//abstract public static function create( $chunks = null );

	/**
	 * Возвращает массив всех загруженных в данный момент объектов данного типа
	 * @return array
	 */
	public static function getList() {

		return self::$list;
	}

	/**
	 * Возвращает имя из чанка (при необходимости убирает `)
	 * @param $name
	 * @return string
	 */
	public static function chunk2name( $name ) {

		if( mb_substr( $name, 0, 1 ) == '`' and mb_substr( $name, -1 ) == '`' ) {
			$name = mb_substr( $name, 1, strlen( $name ) - 2 );
		}
		return $name;
	}
}