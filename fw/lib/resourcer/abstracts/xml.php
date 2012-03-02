<?php

namespace Difra\Resourcer\Abstracts;

abstract class XML extends Common {
	
	protected function processData( $instance ) {
		
		$files = $this->getFiles( $instance );
		
		$newXml = new \SimpleXMLElement("<{$this->type}></{$this->type}>");
		foreach( $files as $file ) {
			$xml = simplexml_load_file( $file['raw'] );
			$this->_mergeXML( $newXml, $xml	);
			foreach( $xml->attributes() as $key => $value ) {
				$newXml->addAttribute( $key, $value );
			}
		}
		if( method_exists( $this, 'postprocess' ) ) {
			$this->postprocess( $newXml, $instance );
		}
		return $newXml->asXML();
	}
	
	private function _mergeXML( &$xml1, &$xml2 ) {
		
		foreach( $xml2 as $name => $node ) {
			if( property_exists( $xml1, $name ) ) {
				$attr = $xml1->$name->attributes();
				foreach( $node->attributes() as $key => $value ) {
					if( !isset( $attr[$key] ) ) {
						$xml1->$name->addAttribute( $key, $value );
					} elseif( $value != '' ) {
						$xml1->$name->attributes()->$key = $value;
					}
				}
				$this->_mergeXML( $xml1->$name, $node );
			} else {
				$new = $xml1->addChild( $name, trim( $node ) ? $node : '' );
				foreach( $node->attributes() as $key => $value ) {
					$new->addAttribute( $key, $value );
				}
				$this->_mergeXML( $new, $node );
			}
		}
	}
			
}
