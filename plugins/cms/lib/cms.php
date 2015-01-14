<?php

namespace Difra\Plugins;

/**
 * Class CMS
 *
 * @package Difra\Plugins\CMS
 */
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
	 * Detect if CMS page is requested
	 */
	public static function run() {

		if($page = \Difra\Plugins\CMS\Page::find()) {
			\Difra\Envi\Action::setCustomAction('\Difra\Plugins\CMS\Controller', 'pageAction', array($page));
		}
	}

	/**
	 * Add menus to output XML
	 */
	public static function addMenuXML() {

		if(\Difra\View::$instance == 'adm') {
			return;
		}
		$controller = \Difra\Controller::getInstance();
		self::getMenuXML($controller->realRoot, true);
	}

	/**
	 * Get all menus with all elements
	 *
	 * @param \DOMElement $node
	 * @return bool
	 */
	public static function getMenuXML($node) {

		$data = \Difra\Plugins\CMS\Menu::getList();
		if(empty($data)) {
			return false;
		}
		foreach($data as $menu) {
			/** @var \DOMElement $menuNode */
			$menuNode = $node->appendChild($node->ownerDocument->createElement('CMSMenu'));
			$menu->getXML($menuNode);
			self::getMenuItemsXML($menuNode, $menu->getId());
		}
		return true;
	}

	/**
	 * Get menu items
	 *
	 * @param \DOMNode $node
	 * @param          $menuId

	 * @return bool
	 */
	public static function getMenuItemsXML($node, $menuId) {

		$data = \Difra\Plugins\CMS\Menuitem::getList($menuId);
		if(empty($data)) {
			return false;
		}
		foreach($data as $item) {
			/** @var $itemNode \DOMElement */
			$itemNode = $node->appendChild($node->ownerDocument->createElement('menuitem'));
			$item->getXML($itemNode);
		}
		return true;
	}

	/**
	 * Add text snippets to output XML
	 */
	public static function addSnippetsXML() {

		if(\Difra\View::$instance != 'main') {
			return;
		}

		$controller = \Difra\Controller::getInstance();
		$snippetNode = $controller->realRoot->appendChild($controller->xml->createElement('snippets'));
		\Difra\Plugins\CMS\Snippet::getAllXML($snippetNode);
	}

	/**
	 * Get URL list for sitemap
	 * @return array
	 */
	public static function getSitemap() {

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch('SELECT `tag` FROM `cms`');
		$res = array();
		if(empty($data)) {
			return false;
		}
		$host = 'http://' . \Difra\Envi::getHost();
		foreach($data as $t) {
			$res[] = array('loc' => $host . $t['tag']);
		}
		return $res;
	}

	/**
	 * Get pages list
	 *
	 * @param \DOMElement|\DOMNode $node
	 * @param bool|int             $visible

	 * @return bool
	 */
	public function getListXML($node, $visible = null) {

		$data = \Difra\Plugins\CMS\Page::getList($visible);
		if(empty($data)) {
			return false;
		}
		foreach($data as $page) {
			$pageNode = $node->appendChild($node->ownerDocument->createElement('page'));
			$page->getXML($pageNode);
		}
		return true;
	}

	/**
	 * Get menu list
	 *
	 * @param \DOMNode $node

	 * @return bool
	 */
	public function getMenuListXML($node) {

		$data = \Difra\Plugins\CMS\Menu::getList();
		if(empty($data)) {
			return false;
		}
		foreach($data as $menu) {
			/** @var \DOMElement $menuNode */
			$menuNode = $node->appendChild($node->ownerDocument->createElement('menuobj'));
			$menu->getXML($menuNode);
		}
		return true;
	}

	/**
	 * Get menu item
	 *
	 * @param \DOMElement $node
	 * @param int         $id
	 */
	public function getMenuItemXML($node, $id) {

		\Difra\Plugins\CMS\Menuitem::get($id)->getXML($node);
	}

	/**
	 * Get menu items for parent menu of menu element
	 *
	 * @param \DOMElement $node
	 * @param int         $id
	 */
	public function getAvailablePagesForItemXML($node, $id) {

		$item = \Difra\Plugins\CMS\Menuitem::get($id);
		$this->getAvailablePagesXML($node, $item->getMenuId());
	}

	/**
	 * Get pages list
	 *
	 * @param \DOMElement $node
	 * @param int         $menuId
	 */
	public function getAvailablePagesXML($node, $menuId) {

		$current = \Difra\Plugins\CMS\Menuitem::getList($menuId);
		$currentIds = array();
		if(!empty($current)) {
			foreach($current as $item) {
				$currentIds[] = $item->getPage();
			}
		}
		$all = \Difra\Plugins\CMS\Page::getList(true);
		if(!empty($all)) {
			foreach($all as $item) {
				if(in_array($item->getId(), $currentIds)) {
					continue;
				}
				/** @var $pageNode \DOMElement */
				$pageNode = $node->appendChild($node->ownerDocument->createElement('page'));
				$item->getXML($pageNode);
			}
		}
	}
}
