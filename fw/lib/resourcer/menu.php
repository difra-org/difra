<?php

namespace Difra\Resourcer;

class Menu extends Abstracts\XML {
	
	protected $type = 'menu';
	protected $printable = false;
	
	protected function postprocess( $xml, $instance ) {
		
		$xml->addAttribute( 'instance', $instance );
		if( $xml->attributes()->prefix ) {
			$prefix = $xml->attributes()->prefix;
		} else {
			$prefix = '/' . $instance;
		}
		$this->_recursiveProcessor( $xml, $prefix, 'menu' );
	}
	private function _recursiveProcessor( $node, $href, $prefix ) {
		
		foreach( $node as $subname => $subnode ) {
			$newHref = "$href/$subname";
			$newPrefix = "{$prefix}_{$subname}";
			$subnode->addAttribute( 'id', $newPrefix );
			if( !isset( $subnode->attributes()->href ) ) {
				$subnode->addAttribute( 'href', $newHref );
			};
			$this->_recursiveProcessor( $subnode, $newHref, $newPrefix );
		}
	}
}