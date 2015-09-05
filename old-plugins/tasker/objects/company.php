<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Company
 * @package Difra\Plugins\Tasker\Objects
 */
class Company extends \Difra\Unify
{
	static public $objKey = 'company';
	static protected $table = 'companies';
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
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
