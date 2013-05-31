<?php

use Difra\Plugins\CMS;

class AdmContentMenuController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	/**
	 * Список меню
	 */
	public function indexAction() {

		$listNode = $this->root->appendChild( $this->xml->createElement( 'CMSMenuList' ) );
		\Difra\Plugins\CMS::getInstance()->getMenuListXML( $listNode );
	}

	/**
	 * Список элементов меню
	 * @param Difra\Param\AnyInt $menuId
	 */
	public function viewAction( \Difra\Param\AnyInt $menuId ) {

		/** @var $menuNode \DOMElement */
		$menuNode = $this->root->appendChild( $this->xml->createElement( 'CMSMenuItems' ) );
		$menuNode->setAttribute( 'id', $menuId );
		if( !\Difra\Plugins\CMS::getInstance()->getMenuItemsXML( $menuNode, $menuId->val() ) ) {
			//$this->view->httpError( 404 );
		}
	}

	/**
	 * Форма добавления элемента меню
	 * @param Difra\Param\AnyInt $menuId
	 */
	public function addAction( \Difra\Param\AnyInt $menuId ) {

		/** @var $addNode \DOMElement */
		$addNode = $this->root->appendChild( $this->xml->createElement( 'CMSMenuItemAdd' ) );
		$addNode->setAttribute( 'id', $menuId->val() );
		\Difra\Plugins\CMS::getInstance()->getAvailablePagesXML( $addNode, $menuId->val() );
	}

	/**
	 * Форма редактирования элемента меню
	 * @param Difra\Param\AnyInt $id
	 */
	public function editAction( \Difra\Param\AnyInt $id ) {

		/** @var $editNode \DOMElement */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'CMSMenuItemEdit' ) );
		\Difra\Plugins\CMS::getInstance()->getMenuItemXML( $editNode, $id->val() );
		\Difra\Plugins\CMS::getInstance()->getAvailablePagesForItemXML( $editNode, $id->val() );
	}

	/**
	 * Сохранение страницы (как элемента меню)
	 * @param Difra\Param\AjaxInt $menu
	 * @param Difra\Param\AjaxInt $page
	 * @param Difra\Param\AjaxInt $id
	 * @param Difra\Param\AjaxInt $parent
	 */
	public function savepageAjaxAction( \Difra\Param\AjaxInt $menu,
					    \Difra\Param\AjaxInt $page,
					    \Difra\Param\AjaxInt $id = null,
					    \Difra\Param\AjaxInt $parent = null ) {

		if( $id ) {
			$item = \Difra\Plugins\CMS\Menuitem::get( $id->val() );
		} else {
			$item = \Difra\Plugins\CMS\Menuitem::create();
		}
		$item->setMenu( $menu->val() );
		$item->setParent( $parent ? $parent->val() : null );
		$item->setPage( $page->val() );
		$this->ajax->redirect( '/adm/content/menu/view/' . $menu->val() );
	}

	/**
	 * Сохранение ссылки (как элемента меню)
	 * @param Difra\Param\AjaxInt    $menu
	 * @param Difra\Param\AjaxString $link
	 * @param Difra\Param\AjaxString $label
	 * @param Difra\Param\AjaxInt    $id
	 * @param Difra\Param\AjaxInt    $parent
	 */
	public function savelinkAjaxAction( \Difra\Param\AjaxInt $menu,
					    \Difra\Param\AjaxString $link,
					    \Difra\Param\AjaxString $label,
					    \Difra\Param\AjaxInt $id = null,
					    \Difra\Param\AjaxInt $parent = null ) {

		if( $id ) {
			$item = \Difra\Plugins\CMS\Menuitem::get( $id->val() );
		} else {
			$item = \Difra\Plugins\CMS\Menuitem::create();
		}
		$item->setMenu( $menu->val() );
		$item->setParent( $parent ? $parent->val() : null );
		$item->setLink( $link );
		$item->setLinkLabel( $label );
		$this->ajax->redirect( '/adm/content/menu/view/' . $menu->val() );
	}

	/**
	 * Удаление элемента меню
	 * @param Difra\Param\AnyInt       $id
	 * @param Difra\Param\AjaxCheckbox $confirm
	 */
	public function deleteAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AjaxCheckbox $confirm = null ) {

		if( !$confirm or !$confirm->val() ) {
			$this->ajax->display(
				'<span>'
					. $this->locale->getXPath( 'cms/adm/menuitem/delete-item-confirm' )
					. '</span>'
					. '<form action="/adm/content/menu/delete/' . $id . '" method="post" class="ajaxer">'
					. '<input type="hidden" name="confirm" value="1"/>'
					. '<input type="submit" value="Да"/>'
					. '<a href="#" onclick="ajaxer.close(this)" class="button">Нет</a>'
					. '</form>'
			);
		} else {
			\Difra\Plugins\CMS\Menuitem::get( $id->val() )->delete();
			$this->ajax->refresh();
			$this->ajax->close();
		}
	}

	/**
	 * Перемещение элемента меню вверх
	 * @param Difra\Param\AnyInt $id
	 */
	public function upAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\CMS\Menuitem::get( $id->val() )->moveUp();
		$this->ajax->refresh();
	}

	/**
	 * Перемещение элемента меню вниз
	 * @param Difra\Param\AnyInt $id
	 */
	public function downAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\CMS\Menuitem::get( $id->val() )->moveDown();
		$this->ajax->refresh();
	}
}