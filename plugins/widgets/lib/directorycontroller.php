<?php

namespace Difra\Plugins\Widgets;

use Difra\Ajax;

class DirectoryController extends \Difra\Controller {

	public function indexAjaxAction() {

		$this->subInit();
		$xml = new \DOMDocument();
		$node = $xml->appendChild( $xml->createElement( 'DirectoryWindow' ) );
		Ajax::getInstance()->display( \Difra\View::render( $xml, 'widget_directory', true ) );
	}

	private function subInit() {

		if( !defined( 'static::directory' ) ) {
			throw new \Difra\Exception( 'DirectoryController extended class should have \'directory\' constant with directory name.' );
		}
	}
}