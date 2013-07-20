<?php

class AdmCatalogExtValuesController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction( \Difra\Param\AnyInt $id ) {

		$listNode = $this->root->appendChild( $this->xml->createElement( 'CatalogExtValueList' ) );
		$listNode->setAttribute( 'id', $id );
		$ext = \Difra\Plugins\Catalog\Ext::get( $id );
		$set = $ext->getSet();
		$listNode->setAttribute( 'set', $set );
		$listNode->setAttribute( 'setImages', $set & \Difra\Plugins\Catalog\Ext::SET_IMAGES ? '1' : '0' );
		$listNode->setAttribute( 'name', $ext->getName() );
		\Difra\Plugins\Catalog\Ext::get( $id )->getSetXML( $listNode );
	}

	public function addAction( \Difra\Param\NamedInt $to ) {

		$addNode = $this->root->appendChild( $this->xml->createElement( 'CatalogExtValueAdd' ) );
		$addNode->setAttribute( 'ext', $to->val() );
		$ext = \Difra\Plugins\Catalog\Ext::get( $to->val() );
		$addNode->setAttribute( 'set', $ext->getSet() );
		$addNode->setAttribute( 'extName', $ext->getName() );
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$editNode = $this->root->appendChild( $this->xml->createElement( 'CatalogExtValueEdit' ) );
		\Difra\Plugins\Catalog\Ext::getValueXML( $editNode, $id );
		$ext = \Difra\Plugins\Catalog\Ext::get( $editNode->getAttribute( 'ext' ) );
		$editNode->setAttribute( 'set', $ext->getSet() );
		$editNode->setAttribute( 'extName', $ext->getName() );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $value, \Difra\Param\AjaxInt $ext, \Difra\Param\AjaxFile $image = null,
					\Difra\Param\AjaxInt $id = null ) {

		if( $id ) {
			$ext = \Difra\Plugins\Catalog\Ext::get( $ext );
			$res = $ext->updateValue( $id->val(), $value, $image );
		} else {
			$ext = \Difra\Plugins\Catalog\Ext::get( $ext );
			if( !$image and ( $ext->getSet() & \Difra\Plugins\Catalog\Ext::SET_IMAGES ) ) {
				$this->ajax->required( 'image' );
				return;
			}
			$res = $ext->addValue( $value, $image );
		}
		if( $res == \Difra\Plugins\Catalog\Ext::BAD_IMAGE ) {
			$this->ajax->error( $this->locale->getXPath( 'catalog/adm/ext/bad-image' ) );
			return;
		}
		$this->ajax->redirect( '/adm/catalog/ext/values/' . $ext->getId() );
	}

	public function upAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Ext::moveValueUp( $id );
		$this->ajax->refresh();
	}

	public function downAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Ext::moveValueDown( $id );
		$this->ajax->refresh();
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Ext::deleteValue( $id );
		$this->ajax->refresh();
	}
}