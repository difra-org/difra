<?php

namespace Difra\Plugins\CMS;

/**
 * Class Snippet
 *
 * @package Difra\Plugins\CMS
 */
class Snippet {

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
	 * @static
	 * @param int $id
	 * @return self|null
	 */
	static public function getById( $id ) {

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow( 'SELECT * FROM `cms_snippets` WHERE `id`=\'' . $db->escape( $id ) . "'" );
		return self::data2obj( $data );
	}

	/**
	 * @static
	 * @param string $name
	 * @return Snippet|null
	 */
	static public function getByName( $name ) {

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow( 'SELECT * FROM `cms_snippets` WHERE `name`=\'' . $db->escape( $name ) . "'" );
		return self::data2obj( $data );
	}

	/**
	 * Возвращает xml со всеми сниппетами
	 *
	 * @static
	 * @param \DOMNode $node
	 */
	static public function getAllXML( $node ) {

		$cache = \Difra\Cache::getInstance();
		$cacheKey = 'snippets';
		$res = $cache->get( $cacheKey );
		if( !is_array( $res ) ) {
			try {
				$db = \Difra\MySQL::getInstance();
				$res = $db->fetch( "SELECT `id`, `name`, `text` FROM `cms_snippets`" );
				$cache->put( $cacheKey, $res ? $res : array() );
			} catch( \Difra\Exception $ex ) {
			}
		}
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				/** @var \DOMElement $sNode */
				$sNode = $node->appendChild( $node->ownerDocument->createElement( $data['name'], $data['text'] ) );
				$sNode->setAttribute( 'id', $data['id'] );
			}
		}
	}

	static public function cleanCache() {

		\Difra\Cache::getInstance()->remove( 'snpippets' );
	}

	/**
	 * @static
	 * @return self[]
	 */
	static public function getList() {

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch( 'SELECT * FROM `cms_snippets`' );
		$res = array();
		if( !empty( $data ) ) {
			foreach( $data as $snip ) {
				$res[] = self::data2obj( $snip );
			}
		}
		return $res;
	}

	/**
	 * @static
	 * @param array $data
	 * @return Snippet|null
	 */
	static private function data2obj( $data ) {

		if( empty( $data ) ) {
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
	 * @static
	 * @return Snippet
	 */
	static public function create() {

		return new self;
	}

	public function __destruct() {

		if( !$this->isModified ) {
			return;
		}
		$db = \Difra\MySQL::getInstance();
		if( $this->id ) {
			$db->query( 'UPDATE `cms_snippets` SET `name`=\'' . $db->escape( $this->name ) . "',`text`='"
			. $db->escape( $this->text ) . "',
			`description`='" . $db->escape( $this->description ) . "' WHERE `id`='" . $db->escape( $this->id ) . "'" );
		} else {
			$db->query( 'INSERT INTO `cms_snippets` SET `name`=\'' . $db->escape( $this->name ) . "',`text`='"
			. $db->escape( $this->text ) . "',
			 `description`='" . $db->escape( $this->description ) . "'" );
		}
		$this->cleanCache();
	}

	/**
	 * @return int
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * @param string $name
	 */
	public function setName( $name ) {

		if( $name !== $this->name ) {
			$this->name = $name;
			$this->isModified = true;
		}
	}

	/**
	 * @return string
	 */
	public function getName() {

		return $this->name;
	}

	/**
	 * @param string $text
	 */
	public function setText( $text ) {

		if( $text !== $this->text ) {
			$this->text = $text;
			$this->isModified = true;
		}
	}

	/**
	 * @return string
	 */
	public function getText() {

		return $this->text;
	}

	/**
	 * @param \DOMNode $node
	 */
	public function getXML( $node ) {

		/** @var \DOMElement $sub */
		$sub = $node->appendChild( $node->ownerDocument->createElement( 'snippet', $this->text ) );
		$sub->setAttribute( 'id', $this->id );
		$sub->setAttribute( 'name', $this->name );
		$sub->setAttribute( 'description', $this->description );
	}

	/**
	 * @param $description
	 */
	public function setDescription( $description ) {

		if( $this->description !== $description ) {
			$this->description = $description;
			$this->isModified = true;
		}
	}

	/**
	 * @return string
	 */
	public function getDescription() {

		return $this->description;
	}

	public function del() {

		$this->isModified = false;
		$db = \Difra\MySQL::getInstance();
		$db->query( 'DELETE FROM `cms_snippets` WHERE `id`=\'' . $db->escape( $this->id ) . "'" );
	}
}