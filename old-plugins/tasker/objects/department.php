<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Department
 * @package Difra\Plugins\Tasker\Objects
 */
class Department extends \Difra\Unify
{
	static public $objKey = 'department';
	static protected $table = 'departments';
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'options' => 'auto_increment'
		],
		'company' => 'foreign',
		'department' => [
			'type' => 'foreign',
			'ondelete' => 'null'
		],
		'name' => [
			'type' => 'varchar',
			'length' => 1000,
			'required' => true,
			'index' => true
		]
	];
	static protected $primary = 'id';
}
