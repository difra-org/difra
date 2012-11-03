<?php

class AdmGalleryIndexController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$listNode = $this->root->appendChild( $this->xml->createElement( 'GalleryAlbumList' ) );
		\Difra\Plugins\Gallery::getInstance()->getAlbumsListXML( $listNode );
	}

	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'GalleryAlbumAdd' ) );
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$editNode = $this->root->appendChild( $this->xml->createElement( 'GalleryAlbumEdit' ) );
		\Difra\Plugins\Gallery::getInstance()->getAlbumXML( $editNode, $id->val() );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxString $description, \Difra\Param\AjaxCheckbox $hidden,
				    \Difra\Param\AjaxInt $id = null ) {

		if( $id ) {
			\Difra\Plugins\Gallery::getInstance()->albumUpdate( $id->val(), $name->val(), $description->val(), !$hidden->val() );
		} else {
			\Difra\Plugins\Gallery::getInstance()->albumAdd( $name->val(), $description->val(), !$hidden->val() );
		}
		$this->ajax->redirect( '/adm/gallery' );
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Gallery::getInstance()->albumDelete( $id->val() );
		$this->ajax->redirect( '/adm/gallery' );
	}

	public function upAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Gallery::getInstance()->albumUp( $id->val() );
		$this->ajax->redirect( '/adm/gallery' );
	}

	public function downAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Gallery::getInstance()->albumDown( $id->val() );
		$this->ajax->redirect( '/adm/gallery' );
	}
}