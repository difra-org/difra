<?php

namespace Difra\Plugins\Widgets;

abstract class DirectoryController extends \Difra\Controller {

	const directory = null;

	abstract public function action( $value );

	public function indexAjaxAction() {

		$this->subInit();
		\Difra\Ajax::getInstance()->display( $this->renderWindow() );
	}

	public function addAjaxAction( \Difra\Param\AjaxString $search ) {

		$this->subInit();
		/** @var \Difra\Plugins\Widgets\Objects\Directory $class */
		$class = \Difra\Unify\Storage::getClass( 'WidgetsDirectory' );
		if( strlen( $search ) > $class::DIRECTORY_LENGTH ) {
			\Difra\Ajax::getInstance()->notify(
				\Difra\Locales::getInstance()->getXPath( 'widgets/directory/value-too-long' )
			);
		}
		$searchObj = new \Difra\Unify\Search( 'WidgetsDirectory' );
		$searchObj->addConditions( array( 'directory' => static::directory, 'name' => $search ) );
		$res = $searchObj->doQuery();
		if( empty( $res ) ) {
			$object = $class::create();
			$object->directory = static::directory;
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
			if( $object->directory != static::directory ) {
				throw new \Difra\Exception( 'This item does not exist in this directory.' );
			}
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
			if( $object->directory != static::directory ) {
				throw new \Difra\Exception( 'This item does not exist in this directory.' );
			}
			$object->delete();
		} catch( \Difra\Exception $ex ) {
		}
		\Difra\Ajax::getInstance()->load( '#DirectoryWindow', $this->renderWindow() );
	}

	private function renderWindow() {

		$xml = new \DOMDocument();
		$node = $xml->appendChild( $xml->createElement( 'DirectoryWindow' ) );
		$search = new \Difra\Unify\Search( 'WidgetsDirectory' );
		$search->addCondition( 'directory', static::directory );
		$search->getListXML( $node );
		return \Difra\View::render( $xml, 'widget_directory', true );
	}

	private function subInit() {

		if( !static::directory ) {
			throw new \Difra\Exception( 'DirectoryController extended class should have \'directory\' constant with directory name.' );
		}
		if( strlen( static::directory ) > ( $len = \Difra\Plugins\Widgets\Objects\Directory::DIRECTORY_LENGTH ) ) {
			throw new \Difra\Exception( 'WidgetsDirectory directory name is too long. ' . $len . ' bytes is the limit.' );
		}
	}
}