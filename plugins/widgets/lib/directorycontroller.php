<?php

namespace Difra\Plugins\Widgets;

abstract class DirectoryController extends \Difra\Controller {

	abstract public function action( $value );

	public function indexAjaxAction() {

		$this->subInit();
		\Difra\Ajax::getInstance()->display( $this->renderWindow() );
	}

	public function addAjaxAction( \Difra\Param\AjaxString $search ) {

		$this->subInit();
		$class = \Difra\Unify\Storage::getClass( 'WidgetsDirectory' );
		if( !$object = $class::getByField( 'name', $search ) ) {
			$object = $class::create();
			$object->name = (string)$search;
		}
		\Difra\Ajax::getInstance()->close();
		$this->action( (string)$search );
	}

	public function chooseAjaxAction( \Difra\Param\AnyInt $id ) {

		$this->subInit();
		try {
			$class = \Difra\Unify\Storage::getClass( 'WidgetsDirectory' );
			$object = $class::get( (string)$id );
			\Difra\Ajax::getInstance()->close();
			$this->action( $object->name );
		} catch( \Difra\Exception $ex ) {
			\Difra\Ajax::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'widgets/directory/choose-error' ) );
		}
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		$this->subInit();
		try {
			$class = \Difra\Unify\Storage::getClass( 'WidgetsDirectory' );
			$object = $class::get( (string)$id );
			$object->delete();
		} catch( \Difra\Exception $ex ) {
		}
		\Difra\Ajax::getInstance()->load( '#DirectoryWindow', $this->renderWindow() );
	}

	private function renderWindow() {

		$xml = new \DOMDocument();
		$node = $xml->appendChild( $xml->createElement( 'DirectoryWindow' ) );
		$search = new \Difra\Unify\Search( 'WidgetsDirectory' );
		$search->getListXML( $node );
		return \Difra\View::render( $xml, 'widget_directory', true );
	}

	private function subInit() {

		if( !defined( 'static::directory' ) ) {
			throw new \Difra\Exception( 'DirectoryController extended class should have \'directory\' constant with directory name.' );
		}
	}
}