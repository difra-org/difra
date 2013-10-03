<?php

namespace Difra\Plugins\Catalog;

class Category {

	public $id = null;
	public $name = null;
	public $visible = true;
	/** @var int|null */
	public $parent = null;
	public $link = null;

	public $empty = false;
	public $emptyHidden = false;

	private $loaded = true;
	private $modified = false;

	static $cache = array();
	static $cacheLinks = array();
	static $cacheParents = array();

	public static function create() {

		return new self;
	}

	/**
	 * @static
	 * @param int $id
	 * @return Category
	 */
	public static function get( $id ) {

		if( $id instanceof \Difra\Param\AnyInt ) {
			$id = $id->val();
		}
		if( !empty( self::$cache[$id] ) ) {
			return self::$cache[$id];
		}
		$category = new self;
		$category->id = $id;
		$category->loaded = false;
		self::$cache[$id] = $category;
		return $category;
	}

	/**
	 * Возвращает список категорий
	 *
	 * @param bool|null $visible	null — все категории, true — видимые категории, false — скрытые категории
	 *
	 * @return Category[]
	 */
	public static function getList( $visible = null ) {

		static $_list = array();
		$cacheKey = is_null( $visible ) ? 'null' : ( $visible ? '1' : '0' );
		if( isset( $_list[$cacheKey] ) ) {
			return $_list[$cacheKey];
		}
		$query = 'SELECT * FROM `catalog_categories`';
		if( ! is_null( $visible ) ) {
			$query .= ' WHERE `visible`=' . ( $visible ? '1' : '0' );
		}
		$query .= ' ORDER BY `position`';
		$db = \Difra\MySQL::getInstance();
		$hideempty = false;
		if( \Difra\Config::getInstance()->getValue( 'catalog', 'hideempty' ) ) {
			$hideemptyRow = $db->fetch( "SELECT DISTINCT `category` FROM `catalog_items` WHERE `visible`='1'" );
			if( !empty( $hideemptyRow ) ) {
				$hideempty = array();
				foreach( $hideemptyRow as $c ) {
					$hideempty[$c['category']] = 1;
				}
			}
		}
		$list = false;
		try {
			$list = $db->fetch( $query );
		} catch( \Difra\Exception $e ) {
			$e->notify();
		};
		if( empty( $list ) ) {
			return false;
		}
		$res = array();
		foreach( $list as $cat ) {
			$category          = new self;
			$category->id      = $cat['id'];
			$category->name    = $cat['name'];
			$category->visible = $cat['visible'] ? true : false;
			$category->parent  = $cat['parent'] ? $cat['parent'] : null;
			$category->link    = $cat['link'] ? $cat['link'] : null;
			$category->loaded  = true;
			if( $hideempty and !isset( $hideempty[$category->id] ) ) {
				$category->empty = true;
			}
			if( empty( self::$cache[$cat['id']] ) or !self::$cache[$cat['id']]->isLoaded() ) {
				self::$cache[$cat['id']] = $category;
				if( $cat['link'] ) {
					self::$cacheLinks[$cat['id']] = $cat['link'];
				}
				if( $category->visible ) {
					self::$cacheParents[$cat['id']] = $category->parent;
				}
			}
			$res[] = $category;
		}
		if( \Difra\Config::getInstance()->getValue( 'catalog', 'hideempty' ) ) {
			self::hideEmpty( $res );
		}
		return $_list[$cacheKey] = $res;
	}

	static private function hideEmpty( &$data, $parent = 0 ) {

		$haveData = false;
		foreach( $data as $category ) {
			if( $category->parent == $parent ) {
				$hasChildData = self::hideEmpty( $data, $category->id );
				if( !$category->empty ) {
					$haveData = true;
				} elseif( !$hasChildData ) {
					$category->emptyHidden = true;
				}
			}
		}
		return $haveData;
	}

	public static function getByLink( $link, $parent = 0 ) {

		self::getList( true ); // init category list
		$ids = array_keys( self::$cacheLinks, $link );
		if( !empty( $ids ) ) {
			foreach( $ids as $v ) {
				$cat = self::get( $v );
				if( $cat->getParent() == $parent ) {
					return $cat;
				}
			}
		}
		/*
		$db = \Difra\MySQL::getInstance();
		$id = $db->fetchOne( "SELECT `id` FROM `catalog_categories` WHERE " .
			       ( $parent ? "`parent`='" . $db->escape( $parent ) . "'" : '`parent` IS NULL' ) .
			       " AND `link`='" . $db->escape( $link ) . "'" );
		if( $id ) {
			return self::get( $id );
		}
		*/
		return null;
	}

	private function load() {

		if( $this->loaded ) {
			return true;
		}
		if( !$this->id ) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow( "SELECT * FROM `catalog_categories` WHERE `id`='" . $db->escape( $this->id ) . "'" );
		if( !$data ) {
			return false;
		}
		$this->name = $data['name'];
		$this->visible = $data['visible'] ? true : false;
		$this->parent = $data['parent'] ? $data['parent'] : null;
		$this->link = $data['link'] ? $data['link'] : null;
		if( $this->link ) {
			self::$cacheLinks[$this->id] = $this->link;
		}
		self::$cacheParents[$this->id] = $this->parent;
		$this->loaded = true;
		return true;
	}

	private function save() {

		$db = \Difra\MySQL::getInstance();
		if( $this->id ) {
			$db->query( "UPDATE `catalog_categories` SET"
				    . " `link`='" . $db->escape( $this->link ) . "',"
				    . " `name`='" . $db->escape( $this->name ) . "',"
				    . " `visible`='" . ( $this->visible ? '1' : '0' ) . "',"
				    . " `parent`=" . ( $this->parent ? "'" . $db->escape( $this->parent ) . "'" : 'NULL' )
				    . " WHERE `id`='" . $db->escape( $this->id ) . "'" );
		} else {
			$position = $db->fetchOne( 'SELECT MAX(`position`) FROM `catalog_categories`' );
			$position = $position ? $position + 1 : 1;
			$db->query( "INSERT INTO `catalog_categories` SET"
				    . " `link`='" . $db->escape( $this->link ) . "',"
				    . " `name`='" . $db->escape( $this->name ) . "',"
				    . " `visible`='" . ( $this->visible ? '1' : '0' ) . "',"
				    . " `parent`=" . ( $this->parent ? "'" . $db->escape( $this->parent ) . "'" : 'NULL' ) . ","
				    . " `position`='" . $db->escape( $position ) . "'" );
			$this->id = $db->getLastId();
		}
	}

	public function __destruct() {

		if( $this->loaded and $this->modified ) {
			$this->save();
		}
	}

	public function getFullLink( $prefix = '/c', $fallback = true ) {

		if( $this->link and $this->parent and $parentlink = self::get( $this->parent )->getFullLink( $prefix, false ) ) {
			return $parentlink . '/' . $this->link;
		} elseif( $this->link and !$this->parent ) {
			return $prefix . '/' . $this->link;
		} elseif( $fallback ) {
			return $prefix . '/' . $this->id;
		} else {
			return false;
		}
	}

	public function getLink() {

		return $this->link;
	}

	public function delete() {

		$this->loaded   = true;
		$this->modified = false;
		$db             = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `catalog_categories` WHERE `id`='" . $db->escape( $this->id ) . "'" );
		unset( $this );
	}

	public function getXML( $node ) {

		if( !$this->load() ) {
			return false;
		}
		static $hideEmpty = -1;
		if( $hideEmpty == -1 ) {
			$hideEmpty = \Difra\Config::getInstance()->getValue( 'catalog', 'hideempty' );
		}
		$node->setAttribute( 'id', $this->id );
		$node->setAttribute( 'name', $this->name );
		$node->setAttribute( 'visible', $this->visible ? '1' : '0' );
		$node->setAttribute( 'parent', $this->parent ? $this->parent : '0' );
		$node->setAttribute( 'link', $this->getFullLink() );
		if( $hideEmpty ) {
			$node->setAttribute( 'empty', $this->empty ? '1' : '0' );
			$node->setAttribute( 'emptyHidden', $this->emptyHidden ? '1' : '0' );
		}
		return true;
	}

	public function getId() {

		if( !$this->id ) {
			$this->save();
		}
		return $this->id;
	}

	public function getName() {

		$this->load();
		return $this->name;
	}

	public function setName( $name ) {

		$this->load();
		if( $this->name == $name ) {
			return;
		}
		$this->name = $name;
		$this->link = \Difra\Locales::getInstance()->makeLink( $name );
		$this->modified = true;
	}

	public function getVisible() {

		$this->load();
		return $this->visible;
	}

	public function setVisible( $visible ) {

		$visible = $visible ? true : false;
		$this->load();
		if( $this->visible == $visible ) {
			return;
		}
		$this->visible = $visible;
		$this->modified = true;
	}

	public function getParent() {

		$this->load();
		return $this->parent;
	}

	public function setParent( $parent ) {

		$this->load();
		if( $this->parent == $parent ) {
			return;
		}
		$this->parent = $parent;
		$this->modified = true;
	}

	public function moveUp() {

		$this->load();
		$db      = \Difra\MySQL::getInstance();
		$items   = $db->fetch( "SELECT `id`,`position` FROM `catalog_categories`"
				       . " WHERE `parent`" . ( $this->parent ? "='" . $db->escape( $this->parent ) . "'" : ' IS NULL' )
				       . " ORDER BY `position`" );
		$newSort = array();
		$pos     = 1;
		$prev    = false;
		foreach( $items as $item ) {
			if( $item['id'] != $this->id ) {
				if( $prev ) {
					$newSort[$prev['id']] = $pos ++;
				}
				$prev = $item;
			} else {
				$newSort[$item['id']] = $pos ++;
			}
		}
		if( $prev ) {
			$newSort[$prev['id']] = $pos;
		}
		foreach( $newSort as $id => $pos ) {
			$db->query( "UPDATE `catalog_categories` SET `position`='$pos' WHERE `id`='" . $db->escape( $id ) . "'" );
		}
	}

	public function moveDown() {

		$this->load();
		$db      = \Difra\MySQL::getInstance();
		$items   = $db->fetch( "SELECT `id`,`position` FROM `catalog_categories`"
				       . " WHERE `parent`" . ( $this->parent ? "='" . $db->escape( $this->parent ) . "'" : ' IS NULL' )
				       . " ORDER BY `position`" );
		$newSort = array();
		$pos     = 1;
		$next    = false;
		foreach( $items as $item ) {
			if( $item['id'] != $this->id ) {
				$newSort[$item['id']] = $pos ++;
				if( $next ) {
					$newSort[$next['id']] = $pos ++;
					$next                 = false;
				}
			} else {
				$next = $item;
			}
		}
		if( $next ) {
			$newSort[$next['id']] = $pos;
		}
		foreach( $newSort as $id => $pos ) {
			$db->query( "UPDATE `catalog_categories` SET `position`='$pos' WHERE `id`='" . $db->escape( $id ) . "'" );
		}
	}

	public function isLoaded() {

		return $this->loaded;
	}

	public static function getSubtree( $id ) {

		$res = array();
		if( is_array( $id ) ) {
			foreach( $id as $v ) {
				$n = self::getSubtree( $v );
				$res = array_merge( $res, $n );
			}
		} else {
			$res[] = $id;
			$subs = array_keys( self::$cacheParents, $id );
			if( !empty( $subs ) ) {
				$n   = self::getSubtree( $subs );
				$res = array_merge( $res, $n );
			}
		}
		return $res;
	}
}