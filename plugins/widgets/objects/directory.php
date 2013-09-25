<?php

namespace Difra\Plugins\Widgets\Objects;

class Directory extends \Difra\Unify\Item {

	static protected $propertiesList = array(

		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		),
		'name' => array(
			'type' => 'char',
			'length' => 250,
			'required' => true,
			'unique' => true
		),
		'value' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		)
	);

	static protected $defaultOrder = 'name';
}