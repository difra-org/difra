<?php

namespace Difra\Plugins\Gallery;

class Album
{
	private $id = null;
	private $name = '';
	private $description = '';
	private $visible = true;
	private $images = [];
	private $loaded = false;
	private $modified = false;
	private $imgSizes = [];
	private $format = 'png';

	public function __construct()
	{

		$this->imgSizes = self::getSizes();
	}

	static public function getSizes()
	{

		$sizes = \Difra\Config::getInstance()->getValue('gallery', 'imgSizes');
		if (!$sizes or empty($sizes)) {
			$sizes = [
				's' => [70, 70],
				'm' => [150, 150],
				'l' => [370, 370],
			];
		}
		$sizes['f'] = false;
		return $sizes;
	}

	/**
	 * Возвращает размеры превью в xml
	 * @param \DOMNode $node
	 */
	public function getSizesXML($node)
	{

		foreach ($this->imgSizes as $size => $sizeData) {
			if (isset($sizeData[0])) {
				$sizeNode = $node->appendChild($node->ownerDocument->createElement($size));
				$sizeNode->setAttribute('width', $sizeData[0]);
				$sizeNode->setAttribute('height', $sizeData[1]);
			}
		}
	}

	static public function create()
	{

		return new self;
	}

	/**
	 * @static
	 * @param int $id
	 * @return self
	 */
	static public function get($id)
	{

		$album = new self;
		$album->id = $id;
		return $album;
	}

	public function load()
	{

		if ($this->loaded) {
			return true;
		}
		if (!$this->id) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow("SELECT `name`,`description`,`visible` FROM `gallery_albums` WHERE `id`='" .
							  $db->escape($this->id) . "'");
		if (empty($data)) {
			return false;
		}
		$this->name = $data['name'];
		$this->description = $data['description'];
		$this->visible = $data['visible'] ? true : false;
		$imgData = $db->fetch("SELECT `id` FROM `gallery_photos` "
							  . "WHERE `album`='" . $db->escape($this->id) . "'"
							  . "ORDER BY `position`");
		if (!empty($imgData)) {
			foreach ($imgData as $img) {
				$this->images[] = $img['id'];
			}
		}
		$format = \Difra\Config::getInstance()->getValue('gallery', 'format');
		if ($format) {
			$this->format = $format;
		}
		$this->loaded = true;
		return true;
	}

	/**
	 * @static
	 * @param bool|null $visible
	 * @param int $page
	 * @param int $perpage
	 * @return self[]
	 */
	static public function getList($visible = null, $page = null, $perpage = null)
	{

		$query = "SELECT `id`,`name`,`description`,`visible` FROM `gallery_albums`";
		if (!is_null($visible)) {
			$query .= ' WHERE `visible`=' . ($visible ? '1' : '0');
		}
		$query .= ' ORDER BY `position`';
		if ($page) {
			$query .= ' LIMIT ' . ((intval($page) - 1) * intval($perpage)) . ',' . intval($perpage);
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch($query);
		if (empty($data)) {
			return false;
		}
		$ids = [];
		$res = [];
		$i = 0;
		$format = \Difra\Config::getInstance()->getValue('gallery', 'format');
		foreach ($data as $row) {
			$album = new self;
			$album->id = $row['id'];
			$album->name = $row['name'];
			$album->description = $row['description'];
			$album->visible = $row['visible'] ? true : false;
			$album->loaded = true;
			if ($format) {
				$album->format = $format;
			}
			$res[$i] = $album;
			$ids[$row['id']] = $i;
			$i++;
		}
		$imgData = $db->fetch("SELECT `album`,`id` FROM `gallery_photos` "
							  . "WHERE `album` IN ('" . implode("','", array_keys($ids)) . "') "
							  . "ORDER BY `position`");
		if (!empty($imgData)) {
			foreach ($imgData as $img) {
				$res[$ids[$img['album']]]->images[] = $img['id'];
			}
		}
		return $res;
	}

	public function save()
	{

		if (!$this->modified) {
			return;
		}
		$db = \Difra\MySQL::getInstance();
		if ($this->id) {
			$db->query("UPDATE `gallery_albums` SET "
					   . "`name`='" . $db->escape($this->name) . "',"
					   . "`description`='" . $db->escape($this->description) . "',"
					   . "`visible`=" . ($this->visible ? '1' : '0')
					   . " WHERE `id`='" . $db->escape($this->id) . "'");
		} else {
			$pos = $db->fetchOne("SELECT MAX(`position`) FROM `gallery_albums`");
			$pos = $pos ? intval($pos) + 1 : 1;
			$db->query("INSERT INTO `gallery_albums` SET "
					   . "`name`='" . $db->escape($this->name) . "',"
					   . "`description`='" . $db->escape($this->description) . "',"
					   . "`visible`=" . ($this->visible ? '1' : '0') . ","
					   . "`position`='" . $pos . "'");
			$this->id = $db->getLastId();
		}
		$this->modified = false;
	}

	public function __destruct()
	{

		$this->save();
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getXML($node)
	{

		$this->load();
		$node->setAttribute('id', $this->id);
		$node->setAttribute('name', $this->name);
		$node->setAttribute('description', $this->description);
		$node->setAttribute('visible', $this->visible ? '1' : '0');
		$node->setAttribute('format', $this->format);
		foreach ($this->images as $img) {
			/** @var \DOMElement $imgNode */
			$imgNode = $node->appendChild($node->ownerDocument->createElement('image'));
			$imgNode->setAttribute('id', $img);
			$imgNode->setAttribute('format', $this->format);
		}
	}

	public function getId()
	{

		$this->load();
		return $this->id;
	}

	public function getName()
	{

		$this->load();
		return $this->name;
	}

	public function setName($name)
	{

		$this->load();
		if ($this->name == $name) {
			return;
		}
		$this->name = $name;
		$this->modified = true;
	}

	public function getDescription()
	{

		$this->load();
		return $this->description;
	}

	public function setDescription($description)
	{

		$this->load();
		if ($this->description == $description) {
			return;
		}
		$this->description = $description;
		$this->modified = true;
	}

	public function getVisible()
	{

		$this->load();
		return $this->visible;
	}

	public function setVisible($visible)
	{

		$this->load();
		if ($this->visible == $visible) {
			return;
		}
		$this->visible = $visible;
		$this->modified = true;
	}

	public function moveUp()
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch("SELECT `id`,`position` FROM `gallery_albums` ORDER BY `position`");
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
			$db->query("UPDATE `gallery_albums` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'");
		}
	}

	public function moveDown()
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch("SELECT `id`,`position` FROM `gallery_albums` ORDER BY `position`");
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
		foreach ($newSort as $id => $pos) {
			$db->query("UPDATE `gallery_albums` SET `position`='$pos' WHERE `id`='" . $db->escape($id) . "'");
		}
	}

	public function delete()
	{

		if (!$this->id) {
			$this->modified = false;
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `gallery_albums` WHERE `id`='" . $db->escape($this->id) . "'");
		return true;
	}

	public function getImages()
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		return $db->fetch("SELECT `id` FROM `gallery_photos` WHERE `album`='" . $db->escape($this->id) .
						  "' ORDER BY `position`");
	}

	/**
	 * @param string|\Difra\Param\AjaxFile|\Difra\Param\AjaxFiles $image
	 */
	public function addImage($image)
	{

		if (!$image) {
			return;
		} elseif ($image instanceof \Difra\Param\AjaxFile) {
			$image = [$image->val()];
		} elseif ($image instanceof \Difra\Param\AjaxFiles) {
			$image = $image->val();
		} elseif (!is_array($image)) {
			$image = [$image];
		}
		$path = DIR_DATA . 'gallery/';
		@mkdir($path, 0777, true);
		$this->save();
		$this->load();
		$Config = \Difra\Config::getInstance();
		$Images = \Difra\Libs\Images::getInstance();
		$waterMarkOn = $Config->getValue('gallery', 'watermark');
		$waterMarkOnPreview = $Config->getValue('gallery', 'waterOnPreview');
		$waterText = $Config->getValue('gallery', 'waterText');
		$watermarkImage = null;
		if (file_exists($path . 'watermark.png')) {
			$watermarkImage = file_get_contents($path . 'watermark.png');
		}

		$db = \Difra\MySQL::getInstance();
		$pos = intval($db->fetchOne("SELECT MAX(`position`) FROM `gallery_photos` WHERE `album`='" .
									$db->escape($this->id) . "'")) + 1;
		foreach ($image as $img) {
			$db->query('INSERT INTO `gallery_photos` SET '
					   . "`album`='" . $db->escape($this->id) . "',"
					   . "`position`='$pos'"
			);
			$imgId = $db->getLastId();
			foreach ($this->imgSizes as $k => $size) {
				if ($size) {

					$tmpImg = $Images->scaleAndCrop($img, $size[0], $size[1], $this->format);
					if ($waterMarkOn && $waterMarkOnPreview) {
						if ($waterText != '') {
							$tmpImg = $Images->setWatermark($tmpImg, $waterText, null, $this->format, 7);
						} elseif ($watermarkImage) {
							$tmpImg = $Images->setWatermark($tmpImg, null, $watermarkImage, $this->format, 7);
						}
					}
					file_put_contents($path . $imgId . $k . '.' . $this->format, $tmpImg);
				} else {

					$tmpImg = $Images->convert($img, $this->format);
					if ($waterMarkOn) {
						if ($waterText != '') {
							$tmpImg = $Images->setWatermark($tmpImg, $waterText, null, $this->format, 15);
						} elseif ($watermarkImage) {
							$tmpImg = $Images->setWatermark($tmpImg, null, $watermarkImage, $this->format, 15);
						}
					}
					file_put_contents($path . $imgId . $k . '.' . $this->format, $tmpImg);
				}
			}
			++$pos;
		}
	}

	public function delImage($id)
	{

		$this->load();
		foreach ($this->imgSizes as $k => $v) {
			@unlink(DIR_DATA . 'gallery/' . $id . $k . '.png');
		}
		$db = \Difra\MySQL::getInstance();
		$db->query('DELETE FROM `gallery_photos` WHERE `id`=\'' . $db->escape($id) . "'");
		return;
	}

	public function imageUp($id)
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch("SELECT `id`,`position` FROM `gallery_photos` WHERE `album`='" . $db->escape($this->id) .
							"' ORDER BY `position`");
		$newSort = [];
		$pos = 1;
		$prev = false;
		foreach ($items as $item) {
			if ($item['id'] != $id) {
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
		foreach ($newSort as $k => $pos) {
			$db->query("UPDATE `gallery_photos` SET `position`='$pos' WHERE `id`='" . $db->escape($k) . "'");
		}
	}

	public function imageDown($id)
	{

		$this->load();
		$db = \Difra\MySQL::getInstance();
		$items = $db->fetch("SELECT `id`,`position` FROM `gallery_photos` WHERE `album`='" . $db->escape($this->id) .
							"' ORDER BY `position`");
		$newSort = [];
		$pos = 1;
		$next = false;
		foreach ($items as $item) {
			if ($item['id'] != $id) {
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
		foreach ($newSort as $k => $pos) {
			$db->query("UPDATE `gallery_photos` SET `position`='$pos' WHERE `id`='" . $db->escape($k) . "'");
		}
	}
}
