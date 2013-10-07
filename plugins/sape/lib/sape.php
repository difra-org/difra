<?php

namespace Difra\Plugins;

class SAPE {

	public static function getXML() {

		$controller = \Difra\Envi\Action::getController();
		$sapeNode = $controller->realRoot->appendChild( $controller->xml->createElement( 'sape' ) );
		$sapeNode->setAttribute( 'html', \Difra\Plugins\SAPE\Links::getHTML() );
	}
}