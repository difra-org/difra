<?php

namespace Difra\Plugins\Catalog;

class Ext
{
	private $id = null;
	private $name = '';
	private $group = null;
	private $visible = true;
	private $set = false;
	const SET = 1;
	const SET_IMAGES = 2;
	private $imgWidth = 32;
	private $imgHeight = 32;
	private $loaded = true;
	private $modified = false;
	private $removeSet = false;
	private $removeSetImages = false;

	public static function create()
	{

		return new self;
	}

	public static function get($id)
	{

		$ext = new self;
		$ext->id = $id;
		$ext->loaded = false;
		return $ext;
	}

	public static function getList($visible = null)
	{

		$db = \Difra\MySQL::getInstance();
		$where = '';
		if (!is_null($visible)) {
			$where = ' WHERE `visible`=' . ($visible ? '1' : '0');
		}
		$data = $db->fetch('SELECT * FROM `catalog_ext`' . $where . ' ORDER BY `position`');
		if (empty($data)) {
			return false;
		}
		$res = [];
		foreach ($data as $extData) {
			$ext = new self;
			$ext->id = $extData['id'];
			$ext->name = $extData['name'];
			$ext->group = $extData['group'] ? $extData['group'] : false;
			$ext->visible = $extData['visible'] ? true : false;
			$ext->set = $extData['set'];
			$ext->loaded = true;
			$res[] = $ext;
		}
		return $res;
	}

	public static function getListXML($node, $visible = null, $withValues = false)
	{

		$list = self::getList($visible);
		if (empty($list)) {
			return;
		}
		foreach ($list as $ext) {
			$extNode = $node->appendChild($node->ownerDocument->createElement('ext'));
			$ext->getXML($extNode, $withValues);
		}
	}

	private function load()
	{

		if ($this->loaded) {
			return true;
		}
		if (!$this->id) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow('SELECT * FROM `catalog_ext` WHERE `id`=\'' . $db->escape($this->id) . "'");
		if (empty($data)) {
			return false;
		}
		$this->name = $data['name'];
		$this->group = $data['group'] ? $data['group'] : null;
		$this->visible = $data['visible'] ? true : false;
		$this->set = $data['set'];
		$this->loaded = true;
		return true;
	}

	private function save()
	{

		if (!$this->loaded) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		if ($this->id and $this->modified) {
			$db->query("UPDATE `catalog_ext` SET "
					   . "`name`='" . $db->escape($this->name) . "',"
					   . "`group`=" . ($this->group ? "'" . $db->escape($this->group) . "'" : 'NULL') . ","
					   . "`visible`=" . ($this->visible ? '1' : '0') . ","
					   . "`set`='" . $db->escape($this->set) . "'"
					   . " WHERE `id`='" . $db->escape($this->id) . "'"
			);
			if ($this->removeSetImages and !($this->set & self::SET_IMAGES)) {
				$this->removeSetImages();
			}
			if ($this->removeSet and !$this->set) {
				$this->removeSet();
			}
			$this->modified = false;
			return true;
		} elseif (!$this->id) {
			$position = $db->fetchOne("SELECT MAX(`position`) FROM `catalog_ext`");
			$position = $position ? $position + 1 : 1;
			$db->query("INSERT INTO `catalog_ext` SET "
					   . "`name`='" . $db->escape($this->name) . "',"
					   . "`group`=" . ($this->group ? "'" . $db->escape($this->group) . "'" : 'NULL') . ","
					   . "`visible`=" . ($this->visible ? '1' : '0') . ","
					   . "`set`='" . $db->escape($this->set) . "',"
					   . "`position`='" . $db->escape($position) . "'"
			);
			$this->id = $db->getLastId();
			$this->modified = false;
			return true;
		}
		return false;
	}

	public function __destruct()
	{

		$this->save();
	}

	private function removeSet()
	{

		$db = \Difra\MySQL::getInstance();
		$db->query('DELETE FROM `catalog_ext_sets` WHERE `ext`=\'' . $db->escape($this->getId()) . "'");
		$this->removeSet = false;
	}

	private function removeSetImages()
	{

		$db = \Difra\MySQL::getInstance();
		$ids = $db->fetch('SELECT `id` FROM `catalog_ext_sets` WHERE `ext`=\'' . $db->escape($this->getId()) . "'");
		if (!empty($ids)) {
			foreach ($ids as $idh) {
				@unlink(DIR_DATA . "catalog/ext/{$idh['id']}.png");
			}
		}
		$this->removeSetImages = false;
	}

	public function getId()
	{

		if (!$this->id) {
			$this->save();
		}
		return $this->id;
	}

	public function getXML($node, $withValues = false)
	{

		$this->load();
		$node->setAttribute('id', $this->getId());
		$node->setAttribute('name', $this->name);
		$node->setAttribute('group', $this->group ? $this->group : '0');
		$node->setAttribute('visible', $this->visible);
		$node->setAttribute('set', $this->set);
		$node->setAttribute('withImages', ($this->set & self::SET_IMAGES) ? '1' : '0');
		if ($withValues) {
			$this->getSetXML($node);
		}
	}

	public function setName($name)
	{

		$this->load();
		if ($name == $this->name) {
			return;
		}
		$this->name = $name;
		$this->modified = true;
	}

	public function getName()
	{

		$this->load();
		return $this->name;
	}

	public function setSet($set)
	{

		$this->load();
		if ($this->set == $set) {
			return;
		}
		if ($this->set and !$set) {
			$this->removeSet = true;
		}
		if (!($set & self::SET_IMAGES) and ($this->set & self::SET_IMAGES)) {
			$this->removeSetImages = true;
		}
		$this->set = $set;
		$this->modified = true;
	}

	public function getSet()
	{

		$this->load();
		return $this->set;
	}

	public function setGroup($group)
	{

		$this->load();
		$group = $group ? $group : null;
		if ($group == $this->group) {
			return;
		}
		$this->group = $group;
		$this->modified = true;
	}

	public function moveUp()
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch('SELECT `id`,`position` FROM `catalog_ext` WHERE `group`' . ($this->group ?
				"='" . $db->escape($this->group) . "'" : ' IS NULL') . ' ORDER BY `position`');
		$newSort = [];
		$pos = 1;
		$prev = false;
		foreach ($data as $extData) {
			if ($extData['id'] != $this->id) {
				if ($prev) {
					$newSort[$prev['id']] = $pos++;
				}
				$prev = $extData;
			} else {
				$newSort[$extData['id']] = $pos++;
			}
		}
		if ($prev) {
			$newSort[$prev['id']] = $pos;
		}
		foreach ($newSort as $id => $pos) {
			$db->query("UPDATE `catalog_ext` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'");
		}
	}

	public function moveDown()
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch('SELECT `id`,`position` FROM `catalog_ext` WHERE `group`' . ($this->group ?
				"='" . $db->escape($this->group) . "'" : ' IS NULL') . ' ORDER BY `position`');
		$newSort = [];
		$pos = 1;
		$next = false;
		foreach ($data as $extData) {
			if ($extData['id'] != $this->id) {
				$newSort[$extData['id']] = $pos++;
				if ($next) {
					$newSort[$next['id']] = $pos++;
					$next = false;
				}
			} else {
				$next = $extData;
			}
		}
		if ($next) {
			$newSort[$next['id']] = $pos;
		}
		foreach ($newSort as $id => $pos) {
			$db->query("UPDATE `catalog_ext` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'");
		}
	}

	public function delete()
	{

		$db = \Difra\MySQL::getInstance();
		if ($this->set & self::SET_IMAGES) {
			$this->removeSetImages();
		}
		$db->query("DELETE FROM `catalog_ext` WHERE `id`='" . $db->escape($this->id) . "'");
		unset($this);
	}

	/**
	 * Функции для работы с наборами значений
 */

	/**
	 * Получить массив со значениями
	 * @return array
	 */
	public function getValues()
	{

		$db = \Difra\MySQL::getInstance();
		return $db->fetch("SELECT * FROM `catalog_ext_sets` WHERE `ext`='" . $db->escape($this->id) .
						  "' ORDER BY `position`");
	}

	/**
	 * Получить набор значений
	 * @param \DOMNode $node
	 */
	public function getSetXML($node)
	{

		$data = $this->getValues();
		if (empty($data)) {
			return;
		}
		foreach ($data as $value) {
			$valueNode = $node->appendChild($node->ownerDocument->createElement('set'));
			$valueNode->setAttribute('id', $value['id']);
			$valueNode->setAttribute('ext', $value['ext']);
			$valueNode->setAttribute('name', $value['name']);
		}
	}

	public static function getValueXML($node, $id)
	{

		$db = \Difra\MySQL::getInstance();
		$db->fetchRowXML($node, "SELECT * FROM `catalog_ext_sets` WHERE `id`='" . $db->escape($id) . "'");
	}

	const BAD_IMAGE = -1;

	public function addValue($value, $image = null)
	{

		$this->load();
		$path = $smallImage = '';
		if ($this->set & self::SET_IMAGES) {
			if (!$image) {
				throw new \Difra\Exception('Can\'t add value without image to set with images');
			}
			$path = DIR_DATA . 'catalog/ext/';
			@mkdir($path, 0777, true);
			$smallImage =
				\Difra\Libs\Images::getInstance()->scaleAndCrop($image, $this->imgWidth, $this->imgHeight, 'png', true);
			if (!$smallImage) {
				return self::BAD_IMAGE;
			}
		}
		$db = \Difra\MySQL::getInstance();
		$pos =
			$db->fetchOne(
				"SELECT MAX(`position`) FROM `catalog_ext_sets` WHERE `ext`='" . $db->escape($this->getId()) . "'");
		$pos = $pos ? intval($pos) + 1 : 1;
		$db->query('INSERT INTO `catalog_ext_sets` SET '
				   . "`ext`='" . $db->escape($this->getId()) . "', "
				   . "`name`='" . $db->escape($value) . "', "
				   . "`position`='$pos'"
		);
		$id = $db->getLastId();
		if ($id and ($this->set & self::SET_IMAGES)) {
			file_put_contents($path . $id . '.png', $smallImage);
		}
		return $id;
	}

	public function updateValue($id, $value, $image = null)
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$db->query(
			"UPDATE `catalog_ext_sets` SET `name`='" . $db->escape($value) . "' WHERE `id`='" . $db->escape($id) . "'");
		if ($image and ($this->set & self::SET_IMAGES)) {
			$path = DIR_DATA . 'catalog/ext/';
			@mkdir($path, 0777, true);
			$smallImage =
				\Difra\Libs\Images::getInstance()->scaleAndCrop($image, $this->imgWidth, $this->imgHeight, 'png', true);
			if (!$smallImage) {
				return self::BAD_IMAGE;
			}
			file_put_contents($path . $id . '.png', $smallImage);
		}
		return $id;
	}

	static public function moveValueUp($id)
	{

		$db = \Difra\MySQL::getInstance();
		$extId = $db->fetchOne('SELECT `ext` FROM `catalog_ext_sets` WHERE `id`=\'' . $db->escape($id) . "'");
		$data =
			$db->fetch('SELECT `id`,`position` FROM `catalog_ext_sets` WHERE `ext`=\'' . $db->escape($extId)
					   . "' ORDER BY `position`");
		$newSort = [];
		$pos = 1;
		$prev = false;
		foreach ($data as $value) {
			if ($value['id'] != $id) {
				if ($prev) {
					$newSort[$prev['id']] = $pos++;
				}
				$prev = $value;
			} else {
				$newSort[$value['id']] = $pos++;
			}
		}
		if ($prev) {
			$newSort[$prev['id']] = $pos;
		}
		$queries = [];
		foreach ($newSort as $id => $pos) {
			$queries[] = "UPDATE `catalog_ext_sets` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'";
		}
		$db->query($queries);
	}

	static public function moveValueDown($id)
	{

		$db = \Difra\MySQL::getInstance();
		$extId = $db->fetchOne('SELECT `ext` FROM `catalog_ext_sets` WHERE `id`=\'' . $db->escape($id) . "'");
		$data =
			$db->fetch('SELECT `id`,`position` FROM `catalog_ext_sets` WHERE `ext`=\'' . $db->escape($extId)
					   . "' ORDER BY `position`");
		$newSort = [];
		$pos = 1;
		$next = false;
		foreach ($data as $value) {
			if ($value['id'] != $id) {
				$newSort[$value['id']] = $pos++;
				if ($next) {
					$newSort[$next['id']] = $pos++;
					$next = false;
				}
			} else {
				$next = $value;
			}
		}
		if ($next) {
			$newSort[$next['id']] = $pos;
		}
		$queries = [];
		foreach ($newSort as $id => $pos) {
			$queries[] = "UPDATE `catalog_ext_sets` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'";
		}
		$db->query($queries);
	}

	static public function deleteValue($id)
	{

		@unlink($path = DIR_DATA . 'catalog/ext/' . intval($id) . '.png');
		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `catalog_ext_sets` WHERE `id`='" . $db->escape($id) . "'");
	}
}
