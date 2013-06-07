<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Employee
 *
 * @package Difra\Plugins\Tasker\Objects
 */
class Employee extends \Difra\Unify {

	static public $objKey = 'employee';
	static protected $table = 'employees';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		),
		'user' => 'foreign'
	);
	static protected $primary = 'id';
}