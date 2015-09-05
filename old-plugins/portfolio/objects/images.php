<?php

namespace Difra\Plugins\Portfolio\Objects;

class Images extends \Difra\Unify
{
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		],
		'portfolio' => [
			'type' => 'int',
			'index' => true,
			'required' => true
		],
		'position' => 'int',
		'image_to_portfolio' => [

			'type' => 'foreign',
			'source' => 'portfolio',
			'target' => 'portfolio_entry',
			'keys' => 'id',
			'onupdate' => 'restrict'
		]
	];
}
