<?php

namespace Difra\Plugins\Portfolio\Objects;

class Portfolio extends \Difra\Unify {

	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		)
	);
}