<?php

abstract class Plugin {

	// Singleton
	public function getInstance() {

		static $_self = null;
		return class_name();
		return $_self ? $_self : $_self = new self;
	}

	public function fillAdmMenu( $nodeName, $subnodeName, $href ) {

		$menu = Menu::getInstance( 'menu-adm.xml' );
		$xml = $menu->menu;
		$root = $xml->documentElement;
		$contentNode = false;
		if( $el = $xml->getElementsByTagname( 'item' ) ) {
			foreach( $el as $item ) {
				if( $item->getAttribute( 'id' ) == $nodeName ) {
					$contentNode = $item;
					break;
				}
			}
		}
		if( !$contentNode ) {
			$contentNode = $root->appendChild( $xml->createElement( 'item' ) );
			$contentNode->setAttribute( 'id', $nodeName );
		}
		$admNode = $contentNode->appendChild( $xml->createElement( 'item' ) );
		$admNode->setAttribute( 'id', $subnodeName );
		$admNode->setAttribute( 'href', $href );
	}
}

