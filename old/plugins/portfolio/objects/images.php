<?php

namespace Difra\Plugins\Portfolio\Objects;

class Images extends \Difra\Unify {

	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		),
		'portfolio' => array(
			'type' => 'int',
			'index' => true,
			'required' => true
		),
		'position' => 'int',

		'image_to_portfolio' => array(

			'type' => 'foreign',
			'source' => 'portfolio',
			'target' => 'portfolio_entry',
			'keys' => 'id',
			'onupdate' => 'restrict'
		)
	);
}