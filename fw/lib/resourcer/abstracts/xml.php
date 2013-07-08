<?php

namespace Difra\Resourcer\Abstracts;

/**
 * Абстрактный класс для работы с ресурсами типа XML
 */
abstract class XML extends Common {

	/**
	 * Сборка ресурсов в единый XML
	 *
	 * @param      $instance
	 * @param bool $withFilenames
	 * @return mixed
	 */
	protected function processData( $instance, $withFilenames = false ) {

		$files = $this->getFiles( $instance );

		$newXml = new \SimpleXMLElement( "<{$this->type}></{$this->type}>" );
		foreach( $files as $file ) {
			$filename = $withFilenames ? $file['raw'] : false;
			$xml = simplexml_load_file( $file['raw'] );
			$this->_mergeXML( $newXml, $xml, $filename );
			foreach( $xml->attributes() as $key => $value ) {
				$newXml->addAttribute( $key, $value );
			}
		}
		if( method_exists( $this, 'postprocess' ) ) {
			$this->postprocess( $newXml, $instance );
		}
		return $newXml->asXML();
	}

	/**
	 * @param \SimpleXMLElement $xml1
	 * @param \SimpleXMLElement $xml2
	 * @param string            $filename
	 */
	private function _mergeXML( &$xml1, &$xml2, &$filename ) {

		/** @var \SimpleXMLElement $node */
		foreach( $xml2 as $name => $node ) {
			if( !$filename and property_exists( $xml1, $name ) ) {
				$attr = $xml1->$name->attributes();
				foreach( $node->attributes() as $key => $value ) {
					if( !isset( $attr[$key] ) ) {
						$xml1->$name->addAttribute( $key, $value );
					} elseif( $value != '' ) {
						$xml1->$name->attributes()->$key = $value;
					}
				}
				$this->_mergeXML( $xml1->$name, $node, $filename );
			} else {
				$new = $xml1->addChild( $name, trim( $node ) ? $node : '' );
				if( $filename ) {
					$new->addAttribute( 'source', $filename );
				}
				foreach( $node->attributes() as $key => $value ) {
					$new->addAttribute( $key, $value );
				}
				$this->_mergeXML( $new, $node, $filename );
			}
		}
	}

}
