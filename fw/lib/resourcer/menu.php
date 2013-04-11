<?php

namespace Difra\Resourcer;

use Difra\Action;

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
		$this->_recursiveProcessor( $xml, $prefix, 'menu', $instance, '/' . Action::getInstance()->getUri() );
	}
	private function _recursiveProcessor( $node, $href, $prefix, $instance, $url ) {

		if( $url == $href ) {
			$node->addAttribute( 'selected', 2 );
		} elseif( mb_substr( $url, 0, mb_strlen( $href ) ) == $href ) {
			$node->addAttribute( 'selected', 1 );
		}
		foreach( $node as $subname => $subnode ) {
			if( $subnode->attributes()->sup and $subnode->attributes()->sup == '1' ) {
				if( !\Difra\Debugger::getInstance()->isEnabled() ) {
					$subnode->addAttribute( 'hidden', 1 );
				}
			}
			$newHref = "$href/$subname";
			$newPrefix = "{$prefix}_{$subname}";
			$subnode->addAttribute( 'id', $newPrefix );
			if( !isset( $subnode->attributes()->href ) ) {
				$subnode->addAttribute( 'href', $newHref );
			};
			$subnode->addAttribute( 'xpath', 'locale/menu/' . $instance . '/' . $newPrefix );
			$this->_recursiveProcessor( $subnode, $newHref, $newPrefix, $instance, $url );
		}
	}
}