<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Priority
 *
 * @package Difra\Plugins\Tasker\Objects
 */
class Priority extends \Difra\Unify {

	static public $objKey = 'priority';
	static protected $table = 'priorities';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'options' => 'auto_increment'
		),
		'weight' => array(
			'type' => 'int',
			'index' => true
		),
		'name' => array(
			'type' => 'char',
			'length' => 64,
			'required' => 1
		),
		'selectable' => array(
			'type' => 'bool',
			'default' => 1,
			'required' => 1
		)
	);
	static protected $primary = 'id';

}