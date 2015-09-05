<?php

namespace Difra\Plugins\Portfolio\Objects;

class Portfolio extends \Difra\Unify
{
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		]
	];
}
