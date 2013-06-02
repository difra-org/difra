<?php

namespace Difra;

/**
 * Class Unify
 *
 * @package Difra\Unify
 */
class Unify extends Unify\Item {

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

//	/** @var array[string $name] */
//	static protected $indexList = null;

//	/** @var string[string] Опции ('engine'=>'InnoDB',etc.) */
//	static protected $options = array();
}