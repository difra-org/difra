<?php

namespace Difra\Adm;

class Localemanage {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function getLocalesList() {

		return \Difra\Resourcer\Locale::getInstance()->findInstances();
	}

	public function getLocale( $locale ) {

		return \Difra\Resourcer::getInstance( 'locale' )->compile( $locale, true );
	}

	public function getLocalesTree() {

		$instances = $this->getLocalesList();
		$locales   = array();
		foreach( $instances as $instance ) {
			$xml = new \DOMDocument();
			$xml->loadXML( $this->getLocale( $instance ) );
			$locales[$instance] = array();
			$this->xml2tree( $xml->documentElement, $locales[$instance], 'locale' );
		}
		return $locales;
	}

	/**
	 * @param \DOMNode $node
	 * @param array    $arr
	 * @param string   $xpath
	 */
	public function xml2tree( $node, &$arr, $xpath ) {

		foreach( $node->childNodes as $item ) {

			switch( $item->nodeType ) {
			case XML_ELEMENT_NODE:
				$this->xml2tree( $item, $arr, $xpath . '/' . $item->nodeName );
				break;
			case XML_TEXT_NODE:
				$source = $node->getAttribute( 'source' );
				$module = $this->getModule( $source );
				if( !isset( $arr[$module] ) ) {
					$arr[$module] = array();
				}
				$arr[$module][$xpath] = array(
					'source' => $source,
					'text' => $item->nodeValue
				);
				break;
			default:
				echo "WTF? " . $item->nodeType . " at " . $xpath . "<br/>";
			}
		}
	}

	public function getModule( $filename ) {

		if( strpos( $filename, DIR_PLUGINS ) === 0 ) {
			$res = substr( $filename, strlen( DIR_PLUGINS ) );
			$res = trim( $res, '/' );
			$res = explode( '/', $res, 2 );
			return 'plugins/' . $res[0];
		} elseif( strpos( $filename, DIR_FW ) === 0 ) {
			return 'fw';
		} elseif( strpos( $filename, DIR_SITE ) == 0 ) {
			return 'site';
		} else {
			return 'unknown';
		}
	}
}