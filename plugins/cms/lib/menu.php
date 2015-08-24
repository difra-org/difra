<?php

namespace Difra\Plugins\CMS;

use Difra, Difra\Plugins;

/**
 * Объект-меню
 */
class Menu
{
	/** @var int */
	private $id = null;
	/** @var string */
	private $name = '';
	/** @var string */
	private $description = '';
	/** @var bool */
	private $modified = false;
	/** @var bool */
	private $loaded = true;

	/**
	 * Create new menu
	 *
	 * @static
	 * @return Menu
	 */
	public static function create()
	{
		return new self;
	}

	/**
	 * Get menu by id
	 *
	 * @param $id
	 * @return Menu
	 */
	public static function get($id)
	{
		$menu = new self;
		$menu->id = $id;
		$menu->loaded = false;
		return $menu;
	}

	/**
	 * Get menu list
	 *
	 * @static
	 * @return Menu[]|bool
	 */
	public static function getList()
	{
		try {
			$cache = \Difra\Cache::getInstance();
			$cacheKey = 'cms_menu_list';
			if (!$data = $cache->get($cacheKey)) {
				$db = \Difra\MySQL::getInstance();
				$data =
					$db->fetch('SELECT * FROM `cms_menu` ORDER BY `name`');
				$cache->put($cacheKey, $data);
			}
			if (!is_array($data) or empty($data)) {
				return false;
			}
			$res = [];
			foreach ($data as $menuData) {
				$menu = new self;
				$menu->id = $menuData['id'];
				$menu->name = $menuData['name'];
				$menu->description = $menuData['description'];
				$menu->loaded = true;
				$res[] = $menu;
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
	 * Save menu data
	 */
	private function save()
	{
		$db = \Difra\MySQL::getInstance();
		if (!$this->id) {
			$db->query(
				'INSERT INTO `cms_menu` SET '
				. "`name`='" . $db->escape($this->name) . "',"
				. "`description`='" . $db->escape($this->description) . "'"
			);
			$this->id = $db->getLastId();
		} else {
			$db->query(
				'UPDATE `cms_menu` SET '
				. "`name`='" . $db->escape($this->name) . "',"
				. "`description`='" . $db->escape($this->description) . "'"
				. " WHERE `id`='" . $db->escape($this->id) . "'"
			);
		}
		self::clearCache();
		$this->modified = false;
	}

	/**
	 * Clear menu caches
	 *
	 * @static
	 */
	public static function clearCache()
	{
		\Difra\Cache::getInstance()->remove('cms_menu_list');
	}

	/**
	 * Get menu data
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
		$node->setAttribute('name', $this->name);
		$node->setAttribute('description', $this->description);
		return true;
	}

	/**
	 * Load menu data
	 *
	 * @return bool
	 */
	private function load()
	{
		if ($this->loaded) {
			return true;
		}
		if (!$this->id) {
			$this->save();
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow("SELECT * FROM `cms_menu` WHERE `id`='" . $db->escape($this->id) . "'");
		if (!$data) {
			return false;
		}
		$this->name = $data['name'];
		$this->description = $data['description'];
		$this->loaded = true;
		return true;
	}

	/**
	 * Delete menu
	 */
	public function delete()
	{
		$this->loaded = true;
		$this->modified = false;
		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `cms_menu` WHERE `id`='" . $db->escape($this->id) . "'");
		self::clearCache();
	}

	/**
	 * Get menu id
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
}
