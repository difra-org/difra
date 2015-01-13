<?php

class AdmCatalogItemsController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function categoryAction( \Difra\Param\AnyInt $id ) {

		/** @var \DOMElement $listNode */
		$listNode = $this->root->appendChild( $this->xml->createElement( 'CatalogItemsList' ) );
		\Difra\Plugins\Catalog::getInstance()->getCategoriesListXML( $listNode );
		$listNode->setAttribute( 'selected', $id->val() );
		\Difra\Plugins\Catalog::getInstance()->getItemsXML( $listNode, $id->val(), true );
	}

	public function addAction( \Difra\Param\NamedInt $to = null ) {

		/** @var \DOMElement $addNode */
		$addNode = $this->root->appendChild( $this->xml->createElement( 'CatalogItemAdd' ) );
		\Difra\Plugins\Catalog::getInstance()->getCategoriesListXML( $addNode );
		if( $to ) {
			$addNode->setAttribute( 'category', $to->val() );
		}
		\Difra\Plugins\Catalog\Ext::getListXML( $addNode, true, true );
	}

	public function saveAjaxAction( \Difra\Param\AjaxInt $category,
					\Difra\Param\AjaxString $name,
					\Difra\Param\AjaxCheckbox $visible,
					\Difra\Param\AjaxInt $id = null,
					\Difra\Param\AjaxFloat $price = null,
					\Difra\Param\AjaxSafeHTML $description = null,
					\Difra\Param\AjaxData $ext = null,
					\Difra\Param\AjaxFile $mainImage = null,
					\Difra\Param\AjaxFiles $images = null ) {

		$ext = $ext ? $ext->val() : null;
		$allowNoImage = \Difra\Config::getInstance()->getValue( 'catalog', 'allownoimage' );

		if( $id ) {
			\Difra\Plugins\Catalog::getInstance()->updateItem( $id, $name, $category, $visible, $price, $description, $ext );
		} else {
			if( !$mainImage && $allowNoImage != 1 ) {
				$this->ajax->required( 'mainImage' );
				return;
			}
			\Difra\Plugins\Catalog::getInstance()->addItem( $name,
				$category,
				$visible,
				$price,
				$description,
				$ext,
				$mainImage,
				$images );
		}
		$this->ajax->redirect( '/adm/catalog/items/category/' . $category );
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog::getInstance()->deleteItem( $id );
		$this->ajax->refresh();
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		/** @var \DOMElement $editNode */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'CatalogItemEdit' ) );
		\Difra\Plugins\Catalog::getInstance()->getCategoriesListXML( $editNode );
		\Difra\Plugins\Catalog\Ext::getListXML( $editNode, true, true );
		$itemNode = $editNode->appendChild( $this->xml->createElement( 'item' ) );
		$item = \Difra\Plugins\Catalog\Item::get( $id->val() );
		$item->loadExt();
		$item->getXML( $itemNode );
		$editNode->setAttribute( 'category', $item->getCategory() );
	}

	public function addimageAjaxAction( \Difra\Param\AjaxInt $id, \Difra\Param\AjaxFiles $images = null ) {

		if( $images ) {
			try {
				\Difra\Plugins\Catalog::getInstance()->addImages( $id, $images );
			} catch( \Difra\Exception $ex ) {
				$ex->notify();
				\Difra\Libs\Cookies::getInstance()->notify( $this->locale->getXPath( 'catalog/adm/ext/bad-image' ) );
			}
			$this->ajax->refresh();
		} else {
			$this->ajax->notify( $this->locale->getXPath( 'catalog/adm/images/no-file' ) );
		}
	}

	public function deleteimageAjaxAction( \Difra\Param\AnyInt $itemId, \Difra\Param\AnyInt $imgId ) {

		\Difra\Plugins\Catalog::getInstance()->deleteImage( $itemId, $imgId );
		$this->ajax->refresh();
	}

	public function setmainimageAjaxAction( \Difra\Param\AnyInt $itemId, \Difra\Param\AnyInt $imgId ) {

		\Difra\Plugins\Catalog::getInstance()->setMainImage( $itemId, $imgId );
		$this->ajax->refresh();
	}
}