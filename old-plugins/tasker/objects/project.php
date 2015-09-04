<?php

namespace Difra\Plugins\Tasker\Objects;

/**
 * Class Project
 *
 * @package Difra\Plugins\Tasker\Objects
 */
class Project extends \Difra\Unify {

	static public $objKey = 'project';
	static protected $table = 'projects';
	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'options' => 'auto_increment'
		),
		'name' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => 1
		)
	);
	static protected $primary = 'id';

}