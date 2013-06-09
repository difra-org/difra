<?php

class TestObj extends \Difra\Unify {

	static public $objKey = 'test';
	static protected $table = 'test_table';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'options' => 'auto_increment'
		),
		'visible' => 'bool',
		'parent' => array(
			'type' => 'foreign',
			'object' => 'test'
		),
		'test2' => 'foreign',
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
	static protected $defaultSearch = array(
		'visible' => 1
	);
}