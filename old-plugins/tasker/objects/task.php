<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Task
 * @package Difra\Plugins\Tasker\Objects
 */
class Task extends \Difra\Unify
{
	static public $objKey = 'task';
	static protected $table = 'tasks';
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'options' => 'auto_increment'
		],
		'project' => 'foreign',
		'priority' => 'foreign',
		'parent' => [
			'type' => 'foreign',
			'object' => 'task'
		],
		// state
		// create date
		'author' => [
			'type' => 'foreign',
			'object' => 'employee',
			'ondelete' => 'null'
		],
		'department' => 'foreign',
		'assignee' => [
			'type' => 'foreign',
			'object' => 'employee',
			'ondelete' => 'null'
		],
		'title' => [
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		],
		//'stage' => 'foreign',
		//'subsystem' => 'foreign'
		// affected versions
		// fixed in build
		// tester
		// verified
		// updated by / versions ???
	];
	static protected $primary = 'id';
}
