<?php

namespace Difra\Plugins\Users\Objects;

class Fields extends \Difra\Unify {

	static protected $propertiesList = array(

		'id' => array(
			'type' => 'int',
			'index' => true,
			'required' => true
		),
		'name' => array(
			'type' => 'char',
			'length' => 64,
			'required' => true,
			'index' => true
		),
		'value' => array(
			'type' => 'text'
		),
		'users_fields_id_name' => array(
			'type' => 'index',
			'columns' => array( 'id', 'name' )
		),
		'users_fields_idfk' => array(
			'type' => 'foreign',
			'source' => 'id',
			'target' => 'users',
			'keys' => 'id',
			'onupdate' => 'restrict'
		)
	);
}