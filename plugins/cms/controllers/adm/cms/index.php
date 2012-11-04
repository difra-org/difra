<?php

use Difra\Plugins\CMS;

class AdmCMSIndexController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	// список страниц
	public function indexAction() {

		$listNode = $this->root->appendChild( $this->xml->createElement( 'CMSList' ) );
		\Difra\Plugins\CMS::getInstance()->getListXML( $listNode );
	}

	// форма добавления страницы
	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'CMSAdd' ) );
	}

	// форма редактирования страницы
	public function editAction( \Difra\Param\AnyInt $id ) {

		/** @var $editNode \DOMElement */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'CMSEdit' ) );
		\Difra\Plugins\CMS\Page::get( $id->val() )->getXML( $editNode );
	}

	// сохранение страницы
	public function saveAjaxAction( \Difra\Param\AjaxString $title,
					\Difra\Param\AjaxString $tag,
					\Difra\Param\AjaxHTML $body,
					\Difra\Param\AjaxInt $id = null ) {

		if( $id ) {
			$page = \Difra\Plugins\CMS\Page::get( $id->val() );
		} else {
			$page = \Difra\Plugins\CMS\Page::create();
		}
		$page->setTitle( $title->val() );
		$page->setUri( $tag->val() );
		$page->setBody( $body );
		\Difra\Ajax::getInstance()->redirect( '/adm/cms' );
	}

	// удаление страницы
	public function deleteAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AjaxCheckbox $confirm = null ) {

		if( $confirm and $confirm->val() ) {
			\Difra\Plugins\CMS\Page::get( $id->val() )->delete();
			$this->ajax->close();
			$this->ajax->redirect( '/adm/cms' );
			return;
		}
		$page = \Difra\Plugins\CMS\Page::get( $id->val() );
		$this->ajax->display(
			'<span>'
			. $this->locale->getXPath( 'cms/adm/delete-page-confirm-1' )
			. $page->getTitle()
			. $this->locale->getXPath( 'cms/adm/delete-page-confirm-2' )
			. '</span>'
			. '<form action="/adm/cms/delete/' . $id . '" method="post" class="ajaxer">'
			. '<input type="hidden" name="confirm" value="1"/>'
			. '<input type="submit" value="Да"/>'
			. '<a href="#" onclick="ajaxer.close(this)" class="button">Нет</a>'
			. '</form>'
		);
	}
}

