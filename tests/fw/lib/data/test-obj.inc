<?php

namespace Objects;

class TestObj extends \Difra\Unify {

	static protected $tableName = 'test_table';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'options' => 'auto_increment'
		),
		'visible' => 'bool',
		'parent' => array(
			'type' => 'int',
			'index' => true
		),
		'parent_ext' => array(
			'type' => 'foreign',
			'source' => 'id',
			'keys' => 'parent'
		),
		'title' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		),
		'description' => array(
			'type' => 'text',
			'autoload' => false
		),
		'ind' => array(
			'type' => 'index',
			'keys' => array( 'id', 'visible', 'title' )
		)
	);
	static protected $primary = 'id';
	static protected ?array $defaultSearch = array(
		'visible' => 1
	);
}