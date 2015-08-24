<?php

namespace Difra\Plugins\CMS;

/**
 * Class Menuitem
 *
 * @package Difra\Plugins\CMS
 */
class Menuitem
{
	/** @var int */
	private $id = null;
	/** @var int */
	private $menu = null;
	/** @var int */
	private $parent = null;
	/** @var bool */
	private $visible = true;
	/** @var int ID страницы */
	private $page = null;
	/** @var array */
	private $pageData = [];
	/** @var string */
	private $link = null;
	/** @var string */
	private $linkLabel = null;
	/** @var bool */
	private $modified = false;
	/** @var bool */
	private $loaded = true;

	/**
	 * Create menu element
	 *
	 * @static
	 * @return Menuitem
	 */
	public static function create()
	{
		return new self;
	}

	/**
	 * Get menu element by id
	 *
	 * @static
	 * @param int $id
	 * @return Menuitem
	 */
	public static function get($id)
	{
		$menuitem = new self;
		$menuitem->id = $id;
		$menuitem->loaded = false;
		return $menuitem;
	}

	/**
	 * Get elements list for menu with id=$menuId
	 *
	 * @static
	 * @param int $menuId
	 * @return Menuitem[]|bool
	 */
	public static function getList($menuId)
	{
		try {
			$cacheKey = 'cms_menuitem_list_' . $menuId;
			$cache = \Difra\Cache::getInstance();
			if (!$data = $cache->get($cacheKey)) {
				$db = \Difra\MySQL::getInstance();
				$data = $db->fetch(
					'SELECT `cms_menu_items`.*,`cms`.`id` as `page_id`,`cms`.`tag`,`cms`.`hidden`,`cms`.`title`'
					. ' FROM `cms_menu_items` LEFT JOIN `cms` ON `cms_menu_items`.`page`=`cms`.`id`'
					. ' WHERE `menu`=\'' . $db->escape($menuId)
					. "' ORDER BY `position`"
				);
				$cache->put($cacheKey, $data);
			}
			if (!is_array($data) or empty($data)) {
				return false;
			}
			$res = [];
			foreach ($data as $menuData) {
				$menuitem = new self;
				$menuitem->id = $menuData['id'];
				$menuitem->menu = $menuData['menu'];
				$menuitem->parent = $menuData['parent'];
				$menuitem->visible = $menuData['visible'];
				$menuitem->page = $menuData['page'];
				if (!empty($menuData['tag'])) {
					$menuitem->pageData = [
						'id'     => $menuData['page_id'],
						'tag'    => $menuData['tag'],
						'hidden' => $menuData['hidden'],
						'title'  => $menuData['title']
					];
				} else {
					$menuitem->link = $menuData['link'];
					$menuitem->linkLabel = $menuData['link_label'];
				}
				$menuitem->loaded = true;
				$res[] = $menuitem;
			}
			return $res;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		if ($this->modified and $this->loaded) {
			$this->save();
		}
	}

	/**
	 * Save menu element data
	 */
	private function save()
	{
		$db = \Difra\MySQL::getInstance();
		if (!$this->id) {
			$pos = $db->fetchOne('SELECT MAX(`position`) FROM `cms_menu_items`');
			$db->query(
				'INSERT INTO `cms_menu_items` SET '
				. "`menu`='" . $db->escape($this->menu) . "',"
				. "`position`=" . $db->escape(intval($pos) + 1) . ","
				. ($this->parent ? "`parent`='" . $db->escape($this->parent) . "'," : '`parent`=NULL,')
				. "`visible`='" . $db->escape($this->visible) . "',"
				. ($this->page ? "`page`='" . $db->escape($this->page) . "'," : '`page`=NULL,')
				. ($this->link ? "`link`='" . $db->escape($this->link) . "'," : '`link`=NULL,')
				. "`link_label`='" . $db->escape($this->linkLabel) . "'"
			);
			$this->id = $db->getLastId();
		} else {
			$db->query(
				'UPDATE `cms_menu_items` SET '
				. "`menu`='" . $db->escape($this->menu) . "',"
				. ($this->parent ? "`parent`='" . $db->escape($this->parent) . "'," : '`parent`=NULL,')
				. "`visible`='" . $db->escape($this->visible) . "',"
				. ($this->page ? "`page`='" . $db->escape($this->page) . "'," : '`page`=NULL,')
				. ($this->link ? "`link`='" . $db->escape($this->link) . "'," : '`link`=NULL,')
				. "`link_label`='" . $db->escape($this->linkLabel) . "'"
				. " WHERE `id`='" . $db->escape($this->id) . "'"
			);
		}
		$this->modified = false;
		$this->clearCache();
	}

	public function clearCache()
	{
		$cache = \Difra\Cache::getInstance();
		$cache->remove('cms_menuitem_' . $this->getId());
		$cache->remove('cms_menuitem_list_' . $this->getMenuId());
	}

	/**
	 * Get menu element id
	 *
	 * @return int
	 */
	public function getId()
	{
		if (!$this->id) {
			$this->save();
		}
		return $this->id;
	}

	/**
	 * Get menu id
	 *
	 * @return int
	 */
	public function getMenuId()
	{
		$this->load();
		return $this->menu;
	}

	/**
	 * Load menu element data
	 *
	 * @return bool
	 */
	private function load()
	{
		if ($this->loaded) {
			return true;
		}
		if (!$this->id) {
			return false;
		}
		$cache = \Difra\Cache::getInstance();
		$cacheKey = 'cms_menuitem_' . $this->id;
		if (!$data = $cache->get($cacheKey)) {
			$db = \Difra\MySQL::getInstance();
			$data = $db->fetchRow("SELECT * FROM `cms_menu_items` WHERE `id`='" . $db->escape($this->id) . "'");
			$cache->put($cacheKey, $data);
		}
		if (!$data) {
			return false;
		}
		$this->menu = $data['menu'];
		$this->parent = $data['parent'];
		$this->visible = $data['visible'];
		$this->page = $data['page'];
		$this->link = $data['link'];
		$this->linkLabel = $data['link_label'];
		$this->loaded = true;
		return true;
	}

	/**
	 * Get menu element data as XML node
	 *
	 * @param \DOMElement $node
	 * @return bool
	 */
	public function getXML($node)
	{
		if (!$this->load()) {
			return false;
		}
		$node->setAttribute('id', $this->id);
		$node->setAttribute('menu', $this->menu);
		$node->setAttribute('parent', $this->parent);
		$node->setAttribute('visible', $this->visible);
		if ($this->page) {
			$node->setAttribute('page', $this->page);
			/** @var $pageNode \DOMElement */
			$pageNode = $node->appendChild($node->ownerDocument->createElement('page'));
			if (!empty($this->pageData)) {
				$pageNode->setAttribute('id', $this->pageData['id']);
				$pageNode->setAttribute('title', $this->pageData['title']);
				$pageNode->setAttribute('uri', $this->pageData['tag']);
				$pageNode->setAttribute('hidden', $this->pageData['hidden']);
			} elseif ($this->page) {
				Page::get($this->page)->getXML($pageNode, true);
			}
		} elseif ($this->link) {
			$node->setAttribute('link', $this->link);
			$node->setAttribute('linkLabel', $this->linkLabel);
		}
		return true;
	}

	/**
	 * Delete menu element
	 */
	public function delete()
	{
		$this->load();
		$this->modified = false;
		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `cms_menu_items` WHERE `id`='" . $db->escape($this->id) . "'");
		$this->clearCache();
	}

	/**
	 * Get page id (or null if element is not a page)
	 *
	 * @return int|null
	 */
	public function getPage()
	{
		$this->load();
		return $this->page;
	}

	/**
	 * Set page id
	 *
	 * @param int|null $page
	 */
	public function setPage($page)
	{
		$this->load();
		if ($page == $this->page) {
			return;
		}
		$this->page = $page;
		$this->modified = true;
	}

	/**
	 * Set parent menu by id
	 *
	 * @param int|null $parent
	 */
	public function setParent($parent)
	{
		$this->load();
		if ($parent == $this->parent) {
			return;
		}
		$this->parent = $parent;
		$this->modified = true;
	}

	/**
	 * Set link by id
	 *
	 * @param string|null $link
	 */
	public function setLink($link)
	{
		$this->load();
		if ($link == $this->link) {
			return;
		}
		$this->link = $link;
		$this->modified = true;
	}

	/**
	 * Set link label
	 *
	 * @param string $label
	 */
	public function setLinkLabel($label)
	{
		$this->load();
		if ($label == $this->linkLabel) {
			return;
		}
		$this->linkLabel = $label;
		$this->modified = true;
	}

	/**
	 * Set menu by id
	 *
	 * @param int $menu
	 */
	public function setMenu($menu)
	{
		$this->load();
		if ($this->menu == $menu) {
			return;
		}
		$this->menu = $menu;
		$this->modified = true;
	}

	/**
	 * Move element up
	 */
	public function moveUp()
	{
		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch(
			"SELECT `id`,`position` FROM `cms_menu_items`"
			. " WHERE `menu`='" . $this->menu . "'"
			. " AND `parent`" . ($this->parent ? "='" . $db->escape($this->parent) . "'" : ' IS NULL')
			. " ORDER BY `position`"
		);
		$newSort = [];
		$pos = 1;
		$prev = false;
		foreach ($items as $item) {
			if ($item['id'] != $this->id) {
				if ($prev) {
					$newSort[$prev['id']] = $pos++;
				}
				$prev = $item;
			} else {
				$newSort[$item['id']] = $pos++;
			}
		}
		if ($prev) {
			$newSort[$prev['id']] = $pos;
		}
		foreach ($newSort as $id => $pos) {
			$db->query("UPDATE `cms_menu_items` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'");
		}
		$this->clearCache();
	}

	/**
	 * Move element down
	 */
	public function moveDown()
	{
		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch(
			"SELECT `id`,`position` FROM `cms_menu_items`"
			. " WHERE `menu`='" . $this->menu . "'"
			. " AND `parent`" . ($this->parent ? "='" . $db->escape($this->parent) . "'" : ' IS NULL')
			. " ORDER BY `position`"
		);
		$newSort = [];
		$pos = 1;
		$next = false;
		foreach ($items as $item) {
			if ($item['id'] != $this->id) {
				$newSort[$item['id']] = $pos++;
				if ($next) {
					$newSort[$next['id']] = $pos++;
					$next = false;
				}
			} else {
				$next = $item;
			}
		}
		if ($next) {
			$newSort[$next['id']] = $pos;
		}
		$queries = [];
		foreach ($newSort as $id => $pos) {
			$queries[] = "UPDATE `cms_menu_items` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'";
		}
		$db->query($queries);
		$this->clearCache();
	}
}
