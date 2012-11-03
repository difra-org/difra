<?php

namespace Difra\Plugins;

class CMS {

	/**
	 * @static
	 * @return CMS
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Определяет, зашел ли пользователь на страницу с CMS
	 */
	public function run() {

		if( $page = \Difra\Plugins\CMS\Page::find() ) {
			$action             = \Difra\Action::getInstance();
			$action->className  = '\Difra\Plugins\CMS\Controller';
			$action->method     = 'pageAction';
			$action->parameters = array( $page );
		}
	}

	/**
	 * Добавляет в XML менюшки
	 */
	public function addMenuXML() {

		if( \Difra\Action::getInstance()->controller->view->instance != 'main' ) {
			return;
		}
		$action   = \Difra\Action::getInstance();
		$rootNode = $action->controller->root;
		$this->getMenuXML( $rootNode, true );
	}

	public function getSnippets() {

		if( \Difra\Action::getInstance()->controller->view->instance != 'main' ) {
			return;
		}

		$action      = \Difra\Action::getInstance();
		$rootNode    = $action->controller->root;
		$snippetNode = $rootNode->appendChild( $rootNode->ownerDocument->createElement( 'snippets' ) );
		\Difra\Plugins\CMS\Snippet::getAllXML( $snippetNode );
	}

	/**
	 * Управление страницами

	 */

	/**
	 * Возвращает список страниц в XML
	 *
	 * @param \DOMElement $node
	 * @param bool|int    $visible
	 *
	 * @return bool
	 */
	public function getListXML( $node, $visible = null ) {

		$data = \Difra\Plugins\CMS\Page::getList( $visible );
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $page ) {
			$pageNode = $node->appendChild( $node->ownerDocument->createElement( 'page' ) );
			$page->getXML( $pageNode );
		}
		return true;
	}

	/**
	 * Управление меню

	 */

	/**
	 * Возвращает список меню в XML
	 *
	 * @param \DOMNode $node
	 *
	 * @return bool
	 */
	public function getMenuListXML( $node ) {

		$data = \Difra\Plugins\CMS\Menu::getList();
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $menu ) {
			/** @var \DOMElement $menuNode */
			$menuNode = $node->appendChild( $node->ownerDocument->createElement( 'menuobj' ) );
			$menu->getXML( $menuNode );
		}
		return true;
	}

	/**
	 * Возвращает все меню со всеми элементами в XML
	 *
	 * @param \DOMElement $node
	 *
	 * @return bool
	 */
	public function getMenuXML( $node ) {

		$data = \Difra\Plugins\CMS\Menu::getList();
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $menu ) {
			/** @var \DOMElement $menuNode */
			$menuNode = $node->appendChild( $node->ownerDocument->createElement( 'CMSMenu' ) );
			$menuNode->setAttribute( 'autorender', '0' );
			$menu->getXML( $menuNode );
			$this->getMenuItemsXML( $menuNode, $menu->getId() );
		}
		return true;
	}

	/**
	 * Управление содержимым меню

	 */

	/**
	 * @param \DOMNode $node
	 * @param          $menuId
	 *
	 * @return bool
	 */
	public function getMenuItemsXML( $node, $menuId ) {

		$data = \Difra\Plugins\CMS\Menuitem::getList( $menuId );
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $item ) {
			/** @var $itemNode \DOMElement */
			$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'menuitem' ) );
			$item->getXML( $itemNode );
		}
		return true;
	}

	/**
	 * Получение в XML информации о пункте меню
	 *
	 * @param \DOMElement $node
	 * @param int         $id
	 */
	public function getMenuItemXML( $node, $id ) {

		\Difra\Plugins\CMS\Menuitem::get( $id )->getXML( $node );
	}

	/**
	 * @param \DOMElement $node
	 * @param int         $menuId
	 */
	public function getAvailablePagesXML( $node, $menuId ) {

		$current    = \Difra\Plugins\CMS\Menuitem::getList( $menuId );
		$currentIds = array();
		if( !empty( $current ) ) {
			foreach( $current as $item ) {
				$currentIds[] = $item->getPage();
			}
		}
		$all = \Difra\Plugins\CMS\Page::getList( true );
		foreach( $all as $item ) {
			if( in_array( $item->getId(), $currentIds ) ) {
				continue;
			}
			/** @var $pageNode \DOMElement */
			$pageNode = $node->appendChild( $node->ownerDocument->createElement( 'page' ) );
			$item->getXML( $pageNode );
		}
	}

	/**
	 * Возвращает список страниц, доступных для добавления в меню, в котором содержится элемент с $id
	 * @param \DOMElement $node
	 * @param int         $id
	 */
	public function getAvailablePagesForItemXML( $node, $id ) {

		$item = \Difra\Plugins\CMS\Menuitem::get( $id );
		$this->getAvailablePagesXML( $node, $item->getMenuId() );
	}
}
