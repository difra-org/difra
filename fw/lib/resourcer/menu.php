<?php

namespace Difra\Resourcer;

use Difra\Envi, Difra\Debugger;

/**
 * Class Menu
 *
 * @package Difra\Resourcer
 */
class Menu extends Abstracts\XML {

	protected $type = 'menu';
	protected $printable = false;

	/**
	 * @param \SimpleXMLElement $xml
	 * @param string            $instance
	 */
	protected function postprocess( $xml, $instance ) {

		$xml->addAttribute( 'instance', $instance );
		/** @noinspection PhpUndefinedFieldInspection */
		if( $xml->attributes()->prefix ) {
			/** @noinspection PhpUndefinedFieldInspection */
			$prefix = $xml->attributes()->prefix;
		} else {
			$prefix = '/' . $instance;
		}
		$this->_recursiveProcessor( $xml, $prefix, 'menu', $instance, '/' . Envi::getUri() );
	}

	/**
	 * @param \SimpleXMLElement $node
	 * @param string            $href
	 * @param string            $prefix
	 * @param string            $instance
	 * @param string            $url
	 */
	private function _recursiveProcessor( $node, $href, $prefix, $instance, $url ) {

		if( $url == $href ) {
			$node->addAttribute( 'selected', 2 );
		} elseif( mb_substr( $url, 0, mb_strlen( $href ) ) == $href ) {
			$node->addAttribute( 'selected', 1 );
		}
		/** @var \SimpleXMLElement $subnode */
		foreach( $node as $subname => $subnode ) {
			/** @noinspection PhpUndefinedFieldInspection */
			if( $subnode->attributes()->sup and $subnode->attributes()->sup == '1' ) {
				if( !Debugger::isEnabled() ) {
					$subnode->addAttribute( 'hidden', 1 );
				}
			}
			$newHref = "$href/$subname";
			$newPrefix = "{$prefix}_{$subname}";
			$subnode->addAttribute( 'id', $newPrefix );
			/** @noinspection PhpUndefinedFieldInspection */
			if( !isset( $subnode->attributes()->href ) ) {
				$subnode->addAttribute( 'href', $newHref );
			};
			$subnode->addAttribute( 'xpath', 'locale/menu/' . $instance . '/' . $newPrefix );
			$this->_recursiveProcessor( $subnode, $newHref, $newPrefix, $instance, $url );
		}
	}
}