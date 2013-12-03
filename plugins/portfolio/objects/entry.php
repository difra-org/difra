<?php

namespace Difra\Plugins\Portfolio\Objects;

/**
 * Class Company
 *
 * @package Difra\Plugins\Portfolio\Objects
 */
class Entry extends \Difra\Unify {

	static protected $propertiesList = array(
		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		),
		'release' => array(
			'type' => 'date',
			'index' => true
		),
		'name' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		),
		'uri' => array(
			'type' => 'varchar',
			'length' => 1000,
			'required' => true,
		),
		'link' => array(
			'type' => 'varchar',
			'length' => 1000,
			'index' => true
		),
		'description' => 'longblob',
		'software' => array(
			'type' => 'char',
			'length' => 250
		),
		'authors' => 'text',
		'portfolio' => array(
			'type' => 'int',
			'index' => true
		),
		'portfolio_ext' => array(
			'type' => 'foreign',
			'source' => 'portfolio',
			'target' => 'PortfolioPortfolio',
			'keys' => 'id'
		)
	);

	protected function afterLoad() {
		/*
		$authors = $this->authors;
		if( $authors and !is_array( $authors ) ) {
			if( $unserializedAuthors = @unserialize( $authors ) ) {
				$this->_data['authors'] = $unserializedAuthors;
			}
		}
		*/
	}
}