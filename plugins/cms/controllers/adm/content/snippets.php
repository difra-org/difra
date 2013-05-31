<?php

use Difra\Plugins\CMS;

class AdmContentSnippetsController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$listNode = $this->root->appendChild( $this->xml->createElement( 'snippetList' ) );
		$list = \Difra\Plugins\CMS\Snippet::getList();
		if( !empty( $list ) ) {
			foreach( $list as $snip ) {
				$snip->getXML( $listNode );
			}
		}
	}

	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'snippetAdd' ) );
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		if( !$snippet = \Difra\Plugins\CMS\Snippet::getById( $id->val() ) ) {
			$this->view->httpError( 404 );
			return;
		}
		/** @var $editNode \DOMElement */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'snippetEdit', $snippet->getText() ) );
		$editNode->setAttribute( 'id', $snippet->getId() );
		$editNode->setAttribute( 'name', $snippet->getName() );
		$editNode->setAttribute( 'description', $snippet->getDescription() );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $name,
					\Difra\Param\AjaxString $text,
					\Difra\Param\AjaxInt $id = null,
					\Difra\Param\AjaxString $description = null ) {

		if( $id ) {
			if( !$snippet = \Difra\Plugins\CMS\Snippet::getById( $id->val() ) ) {
				throw new \Difra\Exception( 'Snippet cannot be saved — bad ID' );
			}
		} else {
			$snippet = \Difra\Plugins\CMS\Snippet::create();
		}
		$snippet->setName( $name );
		$snippet->setDescription( $description );
		$snippet->setText( $text );
		$this->ajax->redirect( '/adm/content/snippets' );
	}

	public function delAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AjaxInt $confirm = null ) {

		if( !$snippet = \Difra\Plugins\CMS\Snippet::getById( $id->val() ) ) {
			$this->ajax->redirect( '/adm/cms/snippets' );
		}
		if( !$confirm ) {
			$this->ajax->confirm( $this->locale->getXPath( 'cms/adm/snippet/del-confirm1' ) .
				$snippet->getName() .
				$this->locale->getXPath( 'cms/adm/snippet/del-confirm2' ) );
			return;
		}
		$snippet->del();
		$this->ajax->close();
		$this->ajax->redirect( '/adm/content/snippets' );
	}
}

