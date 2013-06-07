<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Task
 *
 * @package Difra\Plugins\Tasker\Objects
 */
class Task extends \Difra\Unify {

	static public $objKey = 'task';
	static protected $table = 'tasks';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'options' => 'auto_increment'
		),
		'project' => 'foreign',
		'priority' => 'foreign',
		'parent' => array(
			'type' => 'foreign',
			'object' => 'task'
		),
		// state
		// create date
		'author' => array(
			'type' => 'foreign',
			'object' => 'employee',
			'ondelete' => 'null'
		),
		'department' => 'foreign',
		'assignee' => array(
			'type' => 'foreign',
			'object' => 'employee',
			'ondelete' => 'null'
		),
		'title' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		),
		//'stage' => 'foreign',
		//'subsystem' => 'foreign'
		// affected versions
		// fixed in build
		// tester
		// verified
		// updated by / versions ???
	);
	static protected $primary = 'id';
}