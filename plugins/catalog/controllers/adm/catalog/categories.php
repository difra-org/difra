<?php

use Difra\Plugins\Catalog;

class AdmCatalogCategoriesController extends Difra\Controller {

	public $node;

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$categoriesNode = $this->root->appendChild( $this->xml->createElement( 'CatalogCategories' ) );
		Catalog::getInstance()->getCategoriesListXML( $categoriesNode );
		$conf = \Difra\Config::getInstance()->get( 'catalog' );
		$categoriesNode->setAttribute( 'maxdepth', $conf['maxdepth'] );
	}

	public function addAction( \Difra\Param\NamedInt $to = null ) {

		$addNode = $this->root->appendChild( $this->xml->createElement( 'CatalogCategoriesAdd' ) );
		if( $to ) {
			$addNode->setAttribute( 'parent', $to->val() );
		}
		Catalog::getInstance()->getCategoriesListXML( $addNode );
		$conf = \Difra\Config::getInstance()->get( 'catalog' );
		$addNode->setAttribute( 'maxdepth', $conf['maxdepth'] );
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$cat = \Difra\Plugins\Catalog\Category::get( $id->val() );
		$editNode = $this->root->appendChild( $this->xml->createElement( 'CatalogCategoriesEdit' ) );
		Catalog::getInstance()->getCategoriesListXML( $editNode );
		$cat->getXML( $editNode );
		$conf = \Difra\Config::getInstance()->get( 'catalog' );
		$editNode->setAttribute( 'maxdepth', $conf['maxdepth'] );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxInt $parent = null,
					\Difra\Param\AjaxInt $id = null ) {

		if( !$id ) {
			\Difra\Plugins\Catalog::getInstance()->addCategory( $name->val(), $parent ? $parent->val() : null );
		} else {
			\Difra\Plugins\Catalog::getInstance()->updateCategory( $id->val(), $name->val(), $parent ? $parent->val() : null );
		}
		$this->ajax->redirect( '/adm/catalog/categories' );
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AjaxCheckbox $confirm = null ) {

		if( ! $confirm or ! $confirm->val() ) {
			$cat = \Difra\Plugins\Catalog\Category::get( $id->val() );
			$this->ajax->display( '<span>'
					      . $this->locale->getXPath( 'catalog/adm/category-delete-confirm-1' )
					      . htmlspecialchars( $cat->getName() )
					      . $this->locale->getXPath( 'catalog/adm/category-delete-confirm-2' )
					      . '</span>'
					      . '<form action="/adm/catalog/categories/delete/' . $id . '" method="post" class="ajaxer">'
					      . '<input type="hidden" name="confirm" value="1"/>'
					      . '<input type="submit" value="Да"/>'
					      . '<a href="#" onclick="ajaxer.close(this)" class="button">Нет</a>'
					      . '</form>'
			);
		} else {
			\Difra\Plugins\Catalog::getInstance()->deleteCategory( $id->val() );
			$this->ajax->refresh();
		}
	}

	public function upAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog::getInstance()->moveCategoryUp( $id->val() );
		$this->ajax->refresh();
	}

	public function downAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog::getInstance()->moveCategoryDown( $id->val() );
		$this->ajax->refresh();
	}
}
