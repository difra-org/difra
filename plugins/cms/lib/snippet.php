<?php

/**
 * Snippets are short texts to be used in page layout. For example, copyrights, phone numbers, emails, etc.
 */

namespace Difra\Plugins\CMS;

use Difra\Cache;
use Difra\Exception;
use Difra\MySQL;

/**
 * Class Snippet
 *
 * @package Difra\Plugins\CMS
 */
class Snippet
{
	const CACHE_KEY = 'snippets';
	/** @var int */
	private $id = null;
	/** @var string */
	private $name = null;
	/** @var string */
	private $description = null;
	/** @var string */
	private $text = null;
	/** @var bool */
	private $isModified = false;

	/**
	 * Get snippet by id
	 *
	 * @static
	 * @param int $id
	 * @return self|null
	 */
	static public function getById($id)
	{
		$db = MySQL::getInstance();
		$data = $db->fetchRow('SELECT * FROM `cms_snippets` WHERE `id`=\'' . $db->escape($id) . "'");
		return self::data2obj($data);
	}

	/**
	 * Convert snippets array[] to Snippet[]
	 *
	 * @static
	 * @param array $data
	 * @return Snippet|null
	 */
	static private function data2obj($data)
	{
		if (empty($data)) {
			return null;
		}
		$snippet = new self;
		$snippet->id = $data['id'];
		$snippet->name = $data['name'];
		$snippet->description = $data['description'];
		$snippet->text = $data['text'];
		return $snippet;
	}

	/**
	 * Get snippet by name
	 *
	 * @static
	 * @param string $name
	 * @return Snippet|null
	 */
	static public function getByName($name)
	{
		$db = MySQL::getInstance();
		$data = $db->fetchRow('SELECT * FROM `cms_snippets` WHERE `name`=\'' . $db->escape($name) . "'");
		return self::data2obj($data);
	}

	/**
	 * Get all snippets as XML nodes
	 *
	 * @static
	 * @param \DOMNode $node
	 */
	static public function getAllXML($node)
	{
		$cache = Cache::getInstance();
		$res = $cache->get(self::CACHE_KEY);
		if (!is_array($res)) {
			try {
				$db = MySQL::getInstance();
				$res = $db->fetch("SELECT `id`, `name`, `text` FROM `cms_snippets`");
				$cache->put(self::CACHE_KEY, $res ? $res : []);
			} catch (Exception $ex) {
			}
		}
		if (!empty($res)) {
			foreach ($res as $data) {
				/** @var \DOMElement $sNode */
				$sNode = $node->appendChild($node->ownerDocument->createElement($data['name'], $data['text']));
				$sNode->setAttribute('id', $data['id']);
			}
		}
	}

	/**
	 * Get snippets list
	 *
	 * @static
	 * @return self[]
	 */
	static public function getList()
	{
		$db = MySQL::getInstance();
		$data = $db->fetch('SELECT * FROM `cms_snippets`');
		$res = [];
		if (!empty($data)) {
			foreach ($data as $snip) {
				$res[] = self::data2obj($snip);
			}
		}
		return $res;
	}

	/**
	 * Create snippet
	 *
	 * @static
	 * @return Snippet
	 */
	static public function create()
	{
		return new self;
	}

	/**
	 * Destructor
	 * @throws Exception
	 */
	public function __destruct()
	{
		if (!$this->isModified) {
			return;
		}
		$db = MySQL::getInstance();
		if ($this->id) {
			$db->query(
				'UPDATE `cms_snippets` SET `name`=\'' . $db->escape($this->name) . "',`text`='"
				. $db->escape($this->text) . "',
			`description`='" . $db->escape($this->description) . "' WHERE `id`='" . $db->escape($this->id) . "'"
			);
		} else {
			$db->query(
				'INSERT INTO `cms_snippets` SET `name`=\'' . $db->escape($this->name) . "',`text`='"
				. $db->escape($this->text) . "',
			 `description`='" . $db->escape($this->description) . "'"
			);
		}
		$this->cleanCache();
	}

	/**
	 * Clear cache
	 */
	static public function cleanCache()
	{
		Cache::getInstance()->remove(self::CACHE_KEY);
	}

	/**
	 * Get snippet id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get snippet name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set snippet name
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		if ($name !== $this->name) {
			$this->name = $name;
			$this->isModified = true;
		}
	}

	/**
	 * Get snippet text
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Set snippet text
	 *
	 * @param string $text
	 */
	public function setText($text)
	{
		if ($text !== $this->text) {
			$this->text = $text;
			$this->isModified = true;
		}
	}

	/**
	 * Get snippet as XML node
	 *
	 * @param \DOMNode $node
	 */
	public function getXML($node)
	{
		/** @var \DOMElement $sub */
		$sub = $node->appendChild($node->ownerDocument->createElement('snippet', $this->text));
		$sub->setAttribute('id', $this->id);
		$sub->setAttribute('name', $this->name);
		$sub->setAttribute('description', $this->description);
	}

	/**
	 * Get snippet description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set snippet description
	 *
	 * @param $description
	 */
	public function setDescription($description)
	{
		if ($this->description !== $description) {
			$this->description = $description;
			$this->isModified = true;
		}
	}

	/**
	 * Delete snippet
	 * @throws Exception
	 */
	public function del()
	{
		$this->isModified = false;
		$db = MySQL::getInstance();
		$db->query('DELETE FROM `cms_snippets` WHERE `id`=\'' . $db->escape($this->id) . "'");
	}
}
