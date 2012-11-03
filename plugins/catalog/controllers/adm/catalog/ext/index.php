<?php

class AdmCatalogExtIndexController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$extNode = $this->root->appendChild( $this->xml->createElement( 'CatalogExtList' ) );
		\Difra\Plugins\Catalog\Ext::getListXML( $extNode );
	}

	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'CatalogExtAdd' ) );
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$editNode = $this->root->appendChild( $this->xml->createElement( 'CatalogExtEdit' ) );
		\Difra\Plugins\Catalog\Ext::get( $id->val() )->getXML( $editNode );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxInt $set, \Difra\Param\AjaxInt $group,
					\Difra\Param\AjaxInt $id = null ) {

		if( !$id ) {
			$ext = \Difra\Plugins\Catalog::getInstance()->addExt( $name->val(), $set->val(), $group->val() );
		} else {
			$ext = \Difra\Plugins\Catalog::getInstance()->editExt( $id->val(), $name->val(), $set->val(), $group->val() );
		}
		if( $set->val() ) {
			$this->ajax->redirect( '/adm/catalog/ext/values/' . $ext->getId() );
		} else {
			$this->ajax->redirect( '/adm/catalog/ext' );
		}
	}

	public function upAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Ext::get( $id->val() )->moveUp();
		$this->ajax->refresh();
	}

	public function downAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Catalog\Ext::get( $id->val() )->moveDown();
		$this->ajax->refresh();
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AjaxCheckbox $confirm = null ) {

		if( !$confirm or !$confirm->val() ) {
			$ext = \Difra\Plugins\Catalog\Ext::get( $id->val() );
			$this->ajax->display( '<span>'
					      . $this->locale->getXPath( 'catalog/adm/ext/delete-confirm-1' )
					      . htmlspecialchars( $ext->getName() )
					      . $this->locale->getXPath( 'catalog/adm/ext/delete-confirm-2' )
					      . '</span>'
					      . '<form action="/adm/catalog/ext/delete/' . $id . '" method="post" class="ajaxer">'
					      . '<input type="hidden" name="confirm" value="1"/>'
					      . '<input type="submit" value="Да"/>'
					      . '<a href="#" onclick="ajaxer.close(this)" class="button">Нет</a>'
					      . '</form>'
			);
		} else {
			\Difra\Plugins\Catalog\Ext::get( $id->val() )->delete();
			$this->ajax->refresh();
		}
	}
}
