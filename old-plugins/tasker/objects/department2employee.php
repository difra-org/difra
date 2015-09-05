<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Department2Employee
 * @package Difra\Plugins\Tasker\Objects
 */
class Department2Employee extends \Difra\Unify
{
	static public $objKey = 'department2employee';
	static protected $table = 'department2employee';
	static protected $propertiesList = [
		'department' => 'foreign',
		'employee' => 'foreign',
		'role' => [
			'type' => 'foreign',
			'ondelete' => 'null'
		]
	];
	static protected $primary = ['department', 'employee'];
}
