<?php

namespace Difra\Plugins\Users\Objects;

class Sessions extends \Difra\Unify {

	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'index' => true,
			'required' => true
		),
		'session_id' => array(
			'type' => 'varchar',
			'length' => 48,
			'required' => true
		),
		'date' => array(
			'type' => 'timestamp',
			'default' => 'CURRENT_TIMESTAMP',
			'required' => true
		),
		'ip' => array(
			'type' => 'bigint',
			'required' => true
		),
		'session_id_key' => array(
			'type' => 'primary',
			'columns' => 'session_id'
		),
		'sessionToUser_idfk' => array(
			'type' => 'foreign',
			'source' => 'id',
			'target' => 'users',
			'keys' => 'id',
			'onupdate' => 'restrict'
		)
	);
}