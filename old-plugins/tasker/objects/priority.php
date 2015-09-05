<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Priority
 * @package Difra\Plugins\Tasker\Objects
 */
class Priority extends \Difra\Unify
{
	static public $objKey = 'priority';
	static protected $table = 'priorities';
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'options' => 'auto_increment'
		],
		'weight' => [
			'type' => 'int',
			'index' => true
		],
		'name' => [
			'type' => 'char',
			'length' => 64,
			'required' => 1
		],
		'selectable' => [
			'type' => 'bool',
			'default' => 1,
			'required' => 1
		]
	];
	static protected $primary = 'id';
}
