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
		'link_caption' => 'text',
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

	/**
	 * @param \DOMNode $node
	 */
	protected function postProcessXML( $node ) {

		// дата релиза

		if( !is_null( $this->release ) ) {
			$Locale = \Difra\Locales::getInstance();
			$node->setAttribute( 'release', $Locale->getDateFromMysql( $this->release . ' 00:00:00' ) );
		}

		// авторы

		if( !is_null( $this->authors ) ) {
			$authorsArray = unserialize( $this->authors );
			if( !empty( $authorsArray ) ) {
				foreach( $authorsArray as $k=>$data ) {
					if( isset( $data['role'] ) ) {
						$roleNode = $node->appendChild( $node->ownerDocument->createElement( 'role' ) );
						$roleNode->setAttribute( 'name', $data['role'] );
						if( isset( $data['contibutors'] ) && is_array( $data['contibutors'] ) ) {
							foreach( $data['contibutors'] as $cName ) {
								$cNode = $roleNode->appendChild( $node->ownerDocument->createElement( 'contibutor' ) );
								$cNode->setAttribute( 'name', $cName );
							}
						}
					}
				}
			}
		}
	}

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