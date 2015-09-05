<?php

namespace Difra\Plugins\Portfolio\Objects;

/**
 * Class Company
 * @package Difra\Plugins\Portfolio\Objects
 */
class Entry extends \Difra\Unify
{
	static protected $propertiesList = [
		'id' => [
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		],
		'release' => [
			'type' => 'date',
			'index' => true
		],
		'name' => [
			'type' => 'varchar',
			'length' => 1000,
			'required' => true
		],
		'uri' => [
			'type' => 'varchar',
			'length' => 1000,
			'required' => true,
			'index' => true
		],
		'link' => [
			'type' => 'varchar',
			'length' => 1000,
		],
		'link_caption' => 'text',
		'description' => 'longblob',
		'software' => [
			'type' => 'char',
			'length' => 250
		],
		'authors' => 'text',
		'portfolio' => [
			'type' => 'int',
			'index' => true
		],
		'portfolio_ext' => [
			'type' => 'foreign',
			'source' => 'portfolio',
			'target' => 'PortfolioPortfolio',
			'keys' => 'id'
		]
	];

	/**
	 * @param \DOMNode $node
	 */
	protected function postProcessXML($node)
	{

		// дата релиза

		if (!is_null($this->release)) {
			$release = date('d-m-Y', strtotime($this->release . ' 00:00:00'));
			$node->setAttribute('release', $release);

			$xDate = explode('-', $release);
			$fullDate =
				$xDate[0] . ' ' . \Difra\Locales::getInstance()->getXPath('portfolio/months/m_' . $xDate[1]) . ' ' .
				$xDate[2];
			$node->setAttribute('fullDate', $fullDate);
		}

		// авторы

		if (!is_null($this->authors)) {
			$authorsArray = unserialize($this->authors);
			if (!empty($authorsArray)) {
				foreach ($authorsArray as $k => $data) {
					if (isset($data['role'])) {
						$roleNode = $node->appendChild($node->ownerDocument->createElement('role'));
						$roleNode->setAttribute('name', $data['role']);
						if (isset($data['contibutors']) && is_array($data['contibutors'])) {
							foreach ($data['contibutors'] as $cName) {
								$cNode = $roleNode->appendChild($node->ownerDocument->createElement('contibutor'));
								$cNode->setAttribute('name', $cName);
							}
						}
					}
				}
			}
		}
	}

	protected function afterLoad()
	{
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
