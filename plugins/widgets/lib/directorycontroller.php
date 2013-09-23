<?php

namespace Difra\Plugins\Widgets;

use Difra\Ajax;

class DirectoryController extends \Difra\Controller {

	public function indexAjaxAction() {

		$this->subInit();
		$xml = new \DOMDocument();
		$node = $xml->appendChild( $xml->createElement( 'DirectoryWindow' ) );
		$search = new \Difra\Unify\Search( 'WidgetDirectory' );
		$search->getListXML( $node );
		Ajax::getInstance()->display( \Difra\View::render( $xml, 'widget_directory', true ) );
	}

	public function addAjaxAction( \Difra\Param\AjaxString $search, \Difra\Param\AjaxString $exec = null ) {

		$this->subInit();
		$class = \Difra\Unify\Storage::getClass( 'WidgetDirectory' );
		if( !$object = $class::getByField( 'name', $search ) ) {
			$object = new $class;
			$object->name = (string)$search;
		}
	}

	private function subInit() {

		if( !defined( 'static::directory' ) ) {
			throw new \Difra\Exception( 'DirectoryController extended class should have \'directory\' constant with directory name.' );
		}
	}
}