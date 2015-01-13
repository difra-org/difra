<?php

namespace Difra\Plugins\Users\Objects;

class Recovers extends \Difra\Unify {

	static protected $propertiesList = array(

		'id' => array(
			'type' => 'char',
			'length' => 24,
			'primary' => true
		),
		'used' => array(
			'type' => 'int',
			'length' => 1,
			'required' => true,
			'default' => 0,
			'index' => true
		),
		'userId' => array(
			'type' => 'int',
			'required' => true,
			'index' => 0
		),
		'dateRequested' => array(
			'type' => 'timestamp',
			'default' => 'CURRENT_TIMESTAMP',
			'required' => true
		),
		'dateUsed' => array(
			'type' => 'timestamp'
		),
		'users_recovery_idfk' => array(
			'type' => 'foreign',
			'source' => 'userId',
			'target' => 'users',
			'keys' => 'id',
			'onupdate' => 'restrict'
		)
	);
}