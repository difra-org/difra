<?php
	
class Resourcer_Menu extends Resourcer_Abstract_XML {
	
	protected $type = 'menu';
	protected $printable = false;
	
	protected function postprocess( $xml, $instance ) {
		
		$xml->addAttribute( 'instance', $instance );
		$this->_recursiveProcessor( $xml, "/$instance", 'menu' );
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