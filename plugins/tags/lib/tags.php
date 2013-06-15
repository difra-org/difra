<?php

namespace Difra\Plugins;
use Difra;

class Tags {

	private $modules = array( 'tracks', 'posts' );
	private $maxPt = 30;
	private $minPt = 13;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private function __construct() {

		$config = Difra\Config::getInstance()->get( 'tags' );
		if( !empty( $config ) ) {
			$this->modules = $config['modules'];
			$this->maxPt   = $config['cloud']['max'];
			$this->minPt   = $config['cloud']['min'];
		}
	}

	/**
	 * Tags::saveTag()
	 *
	 * @desc сохраняет тег в базе и возвращает его Id
	 *
	 * @param string $module
	 * @param array  $tags
	 *
	 * @return integer
	 */
	public function saveTags( $module, $tags ) {

		$this->_checkModule( $module );
		if( !is_array( $tags ) ) {
			return false;
		}
		$db = Difra\MySQL::getInstance();

		// забираем альясы тегов
		$tags = $this->getAlias( $tags );

		$tags        = $this->_prepareTag( $tags );
		$tagsidArray = array();

		// сохраняем теги

		$query = array();
		foreach( $tags as $tag ) {
			$query[] = "INSERT IGNORE INTO `{$module}_tags` SET `tag`=" . $tag . ", `link`='" . $this->_makeLink( $tag ) . "'";
		}
		$db->query( $query );

		// забираем теги

		$res = $db->fetch( "SELECT `id`, `tag` FROM `{$module}_tags` WHERE `tag` IN (" . implode( ', ', $tags ) . ")" );
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$tagsidArray[] = $data['id'];
			}
		}
		return !empty( $tagsidArray ) ? $tagsidArray : false;
	}

	private function _prepareTag( $tags ) {

		$db = Difra\MySQL::getInstance();
		foreach( $tags as $k => $tag ) {
			$tag = trim( $tag );
			$tag = mb_strtolower( $tag );

			// Жестко укорачиваем название тега
			//TODO: Может быть в будущем добавить более мягкий способ, например с расчетом пробелов между слов.
			if( mb_strlen( $tag ) >= 25 ) {
				$tag = mb_substr( $tag, 0, 25 );
			}

			$tag      = $db->escape( $tag );
			$tag      = "'" . $tag . "'";
			$tags[$k] = $tag;
		}
		$tags = array_unique( $tags );
		return $tags;
	}

	/**
	 * Tags::assignTags()
	 *
	 * @desc Привязывает теги к сущности
	 *
	 * @param string  $module
	 * @param array   $tagsId
	 * @param integer $itemId
	 *
	 * @return void
	 */
	public function assignTags( $module, $tagsId, $itemId ) {

		$this->_checkModule( $module );
		if( !is_array( $tagsId ) ) {
			return;
		}
		$db = Difra\MySQL::getInstance();

		$query = array();
		foreach( $tagsId as $tagId ) {
			$query[] = "INSERT IGNORE INTO `{$module}_to_tags` SET `tag_id`='" . intval( $tagId ) . "', `item_id`='" . intval( $itemId ) . "'";
		}
		$db->query( $query );
	}

	/**
	 * Tags::get()
	 *
	 * @desc Возвращает теги для сущености
	 *
	 * @param string  $module
	 * @param integer $itemId
	 *
	 * @return array
	 */
	public function get( $module, $itemId ) {

		$this->_checkModule( $module );
		$db  = Difra\MySQL::getInstance();
		$res = $db->fetch( "SELECT t.`tag`, t.`link`
                            FROM `{$module}_to_tags` t2t
                            LEFT JOIN `{$module}_tags` AS `t` ON t2t.`tag_id`=t.`id`
                            WHERE t2t.`item_id`='" . $db->escape( $itemId ) . "'" );
		return isset( $res[0] ) ? $res : false;
	}

	/**
	 * Возвращает XML с тегами итема
	 *
	 * @param \DOMNode $node
	 * @param string   $module
	 * @param int      $itemId
	 */
	public function getXML( $node, $module, $itemId ) {

		$tagsData = $this->get( $module, $itemId );
		if( !empty( $tagsData ) ) {

			$tagsNode = $node->appendChild( $node->ownerDocument->createElement( 'tags' ) );

			foreach( $tagsData as $data ) {
				/** @var \DOMElement $tagItemNode */
				$tagItemNode = $tagsNode->appendChild( $node->ownerDocument->createElement( 'tag' ) );
				foreach( $data as $key=> $value ) {
					$tagItemNode->setAttribute( $key, $value );
				}
			}
		}
	}

	/**
	 * Tags::tagsFromString()
	 *
	 * @desc Возвращает массив тегов из пользовательской строки
	 *
	 * @param mixed $string
	 *
	 * @return array
	 */
	public function tagsFromString( $string ) {

		$tagsArray = array();
		$tags      = preg_replace( '/[^A-Za-zА-Яа-я0-9, -]/u', '', $string );
		$s         = explode( ',', $tags );
		$s         = array_map( 'trim', $s );
		if( !empty( $s ) ) {
			foreach( $s as $string ) {
				if( $string != '' ) {
					$tagsArray[] = $string;
				}
			}
		}
		return !empty( $tagsArray ) ? $tagsArray : false;
	}

	/**
	 * Tags::getMassive()
	 *
	 * @desc Возвращает все теги на большое кол-во записей
	 *
	 * @param string $module
	 * @param array  $itemsArray
	 *
	 * @return array
	 */
	public function getMassive( $module, $itemsArray ) {

		$this->_checkModule( $module );
		$db          = Difra\MySQL::getInstance();
		$itemsArray  = array_map( 'intval', $itemsArray );
		$res         = $db->fetch( "SELECT t.`tag`, t2t.`item_id`
                            FROM `{$module}_to_tags` t2t
                            LEFT JOIN `{$module}_tags` AS `t` ON t2t.`tag_id`=t.`id`
                            WHERE t2t.`item_id` IN(" . implode( ', ', $itemsArray ) . ")" );
		$returnArray = array();
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$returnArray[$data['item_id']][] = $data['tag'];
			}
			return $returnArray;
		}
		return false;
	}

	/**
	 * Возвращает XML с тегами нескольких записей
	 *
	 * @param string   $module
	 * @param \DOMNode $node
	 * @param array    $itemsArray
	 *
	 * @return bool
	 */
	public function getMassiveXml( $module, $node, $itemsArray ) {

		$this->_checkModule( $module );
		$db         = Difra\MySQL::getInstance();
		$itemsArray = array_map( 'intval', $itemsArray );

		$res         = $db->fetch( "SELECT t.`tag`, t2t.`item_id`, t.`link`
                            FROM `{$module}_to_tags` t2t
                            LEFT JOIN `{$module}_tags` AS `t` ON t2t.`tag_id`=t.`id`
                            WHERE t2t.`item_id` IN(" . implode( ', ', $itemsArray ) . ")" );
		$returnArray = array();
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$returnArray[$data['item_id']][] = array( 'name' => $data['tag'], 'link' => $data['link'] );
			}
			foreach( $returnArray as $itemId => $tags ) {
				/** @var \DOMElement $itemNode */
				$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
				$itemNode->setAttribute( 'id', $itemId );
				foreach( $tags as $data ) {
					/** @var \DOMElement $tagNode */
					$tagNode = $itemNode->appendChild( $node->ownerDocument->createElement( 'tag' ) );
					foreach( $data as $key => $value ) {
						$tagNode->setAttribute( $key, mb_strtolower( $value ) );
					}
				}
			}
		}
		return false;
	}

	/**
	 * Tags::update()
	 *
	 * @desc Обновляет теги для записи
	 *
	 * @param string  $module
	 * @param integer $itemId
	 * @param array   $tagsArray
	 *
	 * @return void
	 */
	public function update( $module, $itemId, $tagsArray ) {

		$this->_checkModule( $module );
		$db = Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `{$module}_to_tags` WHERE `item_id`='" . $db->escape( $itemId ) . "'" );

		$tagsArray = $this->saveTags( $module, $tagsArray );
		$this->assignTags( $module, $tagsArray, $itemId );
	}

	/**
	 * Tags::suggest()
	 *
	 * @desc Подсказывает тэги юзеру
	 *
	 * @param string  $module
	 * @param string  $tag
	 * @param integer $limit
	 *
	 * @return array
	 */
	public function suggest( $module, $tag, $limit = 20 ) {

		$db          = Difra\MySQL::getInstance();
		$returnArray = array();
		$this->_checkModule( $module );

		$res =
			$db->fetch( "SELECT `tag` FROM `{$module}_tags` WHERE `tag` LIKE '" . $db->escape( $tag )
				    . "%' ORDER BY `weight` DESC LIMIT " . $limit );

		// забираем альясы
		$aliases = $this->getSuggestAlias( $tag );
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$returnArray[] = $data['tag'];
			}
		}

		if( !empty( $aliases ) ) {
			foreach( $aliases as $oTag => $t ) {
				if( in_array( $oTag, $returnArray ) ) {
					$k                  = array_keys( $returnArray, $oTag );
					$returnArray[$k[0]] = $t;
				} else {
					$returnArray[] = $t;
				}
			}
		}

		$returnArray = array_unique( $returnArray );
		return $returnArray;
	}

	/**
	 * Возвращает альясы на тег для подсказски
	 * @param         $tag
	 * @param integer $limit
	 *
	 * @internal param string $tags
	 * @return array
	 */
	public function getSuggestAlias( $tag, $limit = 10 ) {

		$db          = Difra\MySQL::getInstance();
		$returnArray = array();
		$res         =
			$db->fetch( "SELECT `tag`, `alias` FROM `tags_aliases` WHERE `tag` LIKE '" . $db->escape( $tag ) . "%' LIMIT "
				    . $limit );
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$returnArray[$data['tag']] = $data['alias'];
			}
		}
		return $returnArray;
	}

	/**
	 * Tags::getAlias()
	 *
	 * @desc Возвращает альясы на массив тегов для сохранения
	 *
	 * @param array   $tags
	 * @param integer $limit
	 *
	 * @return array
	 */
	public function getAlias( $tags, $limit = 10 ) {

		$db          = Difra\MySQL::getInstance();
		$returnArray = array();
		$pTags       = $this->_prepareTag( $tags );
		$aliases     = array();
		$res         =
			$db->fetch( "SELECT `tag`, `alias` FROM `tags_aliases` WHERE `tag` IN (" . implode( ', ', $pTags ) . ") LIMIT "
				    . $limit );
		if( !empty( $res ) ) {
			foreach( $res as $data ) {
				$aliases[$data['tag']] = $data['alias'];
			}
		}

		foreach( $tags as $k => $tag ) {
			$returnArray[$k] = isset( $aliases[$tag] ) ? $aliases[$tag] : $tag;
		}
		return !empty( $returnArray ) ? $returnArray : false;
	}

	/**
	 * Создаёт альяс для тега
	 * @param $module
	 * @param $tagId
	 * @param $aliase
	 *
	 * @return bool
	 */
	public function createAliase( $module, $tagId, $aliase ) {

		$db = \Difra\MySQL::getInstance();

		// забираем тег
		$tagData = $db->fetchRow( "SELECT `tag` FROM `{$module}_tags` WHERE `id`='" . intval( $tagId ) . "'" );
		if( !empty( $tagData ) ) {

			// создаём альяс
			$db->query( "INSERT INTO `tags_aliases` (`tag`, `alias`) VALUES ('" .
				    $db->escape( $tagData['tag'] ) . "', '" . $db->escape( $aliase ) . "')" );
			return true;
		}
		return false;
	}

	private function _makeLink( $string ) {

		$link = '';
		$num  = preg_match_all( '/[A-Za-zА-Яа-я0-9]*/u', $string, $matches );
		if( $num and !empty( $matches[0] ) ) {
			$matches = array_filter( $matches[0], 'strlen' );
			$link    = implode( '-', $matches );
		}
		if( $link == '' ) {
			$link = '-';
		}
		return $link;
	}

	private function _checkModule( $module ) {

		if( !in_array( $module, $this->modules ) ) {
			throw new Difra\Exception( "Difra\\Plugins\\Tags: Module '{$module}' not registred in config." );
		}
	}

	/**
	 * Tags::getCloudXml()
	 *
	 * @desc Устанавливаем xml для облака тегов
	 *
	 * @param string      $module
	 * @param \DOMElement $node
	 * @param integer     $limit
	 *
	 * @return array
	 */
	public function getCloudXml( $module, $node, $limit = 25 ) {

		$this->_checkModule( $module );
		$cache    = \Difra\Cache::getInstance();
		$cacheKey = 'tags_cloud_' . $module . '_' . $limit;
		if( !$res = $cache->get( $cacheKey ) ) {
			$db  = Difra\MySQL::getInstance();
			$res = $db->fetch( "SELECT `tag`, `link`, `weight` FROM `{$module}_tags` WHERE `cloud`=1 ORDER BY `tag` ASC LIMIT " . $limit );
			$cache->put( $cacheKey, $res, 57 + rand( 0, 6 ) );
		}
		if( empty( $res ) ) {
			return false;
		}
		$maxWeight = 100;
		/** @var \DOMElement $cloudXml */
		$cloudXml = $node->appendChild( $node->ownerDocument->createElement( 'cloud' ) );
		foreach( $res as $data ) {
			/** @var \DOMElement $tag */
			$tag = $cloudXml->appendChild( $node->ownerDocument->createElement( 'tag' ) );
			$tag->setAttribute( 'tag', $data['tag'] );
			$tag->setAttribute( 'link', rawurlencode( $data['link'] ) );
			if( $maxWeight != 0 ) {
				$p = ( $data['weight'] / $maxWeight ) * 100;
				$tag->setAttribute( 'pt', round( $this->minPt + ( $this->maxPt - $this->minPt ) * $p / 100 ) );
			} else {
				$tag->setAttribute( 'pt', '13' );
			}
		}
		return true;
	}

	/**
	 * Tags::getItemsByLink()
	 *
	 * @desc Возвращает id сущностей входящих в тег по ссылке на тег
	 *
	 * @param string $module
	 * @param string $tagLink
	 *
	 * @return array
	 */
	public function getItemsByLink( $module, $tagLink ) {

		$this->_checkModule( $module );
		$db = Difra\MySQL::getInstance();

		$res = $db->fetch( "SELECT t.`item_id`
							FROM `{$module}_to_tags` t
							LEFT JOIN `{$module}_tags` AS `tt` ON tt.`id`=t.`tag_id`
							WHERE tt.`link`='" . $db->escape( $tagLink ) . "'" );
		if( !empty( $res ) ) {
			$returnArray = array();
			foreach( $res as $data ) {
				$returnArray[] = $data['item_id'];
			}
			return $returnArray;
		}
		return false;
	}

	/**
	 * Tags::getTagByLink()
	 *
	 * @desc возвращает название тега по ссылке на тег
	 *
	 * @param string $module
	 * @param string $tagLink
	 *
	 * @return string
	 */
	public function getTagByLink( $module, $tagLink ) {

		$this->_checkModule( $module );
		$db = Difra\MySQL::getInstance();
		return $db->fetchOne( "SELECT `tag` FROM `{$module}_tags` WHERE `link`='" . $db->escape( $tagLink ) . "'" );
	}

	/**
	 * Возвращает в xml все теги, во всех модулях
	 *
	 * @param \DOMNode $node
	 */
	public function getAllTagsXML( \DOMNode $node, $limit = null ) {

		if( empty( $this->modules ) ) {
			return false;
		}

		$db = Difra\MySQL::getInstance();

		$tagsNode = $node->appendChild( $node->ownerDocument->createElement( 'tags' ) );
		foreach( $this->modules as $module ) {

			$limitString = '';
			if( !is_null( $limit ) ) {
				$limitString = " LIMIT " . intval( $limit );
			}

			$tagsData = $db->fetch( "SELECT `id`, `tag`, `link` FROM `" .
						$db->escape( $module ) . "_tags` ORDER BY `tag` ASC " . $limitString );
			if( !empty( $tagsData ) ) {
				foreach( $tagsData as $data ) {
					$tagNode = $tagsNode->appendChild( $node->ownerDocument->createElement( 'item', \
						addslashes( htmlspecialchars( $data['tag'] ) ) ) );
					$tagNode->setAttribute( 'module', $module );
					foreach( $data as $key=> $value ) {
						if( $key == 'tag' && mb_strlen( $value ) > 25 ) {
							$value = mb_substr( $value, 0, 27 ) . '...';
						}
						if( $key == 'link' ) {
							$value = mb_strtolower( $value );
						}
						$tagNode->setAttribute( $key, $value );
					}
				}
			}
		}
	}

	/**
	 * Возвращает данные тега по его id
	 *
	 * @param string $module
	 * @param int    $tagId
	 *
	 * @return array
	 */
	public function getTag( $module, $tagId ) {

		$db = \Difra\MySQL::getInstance();
		return $db->fetchRow( "SELECT * FROM `" . $db->escape( $module ) . "_tags` WHERE `id`='" . intval( $tagId ) . "'" );
	}

	/**
	 * Сохраняет отредактированные тег
	 * @param $module
	 * @param $tagId
	 * @param $tag
	 *
	 * @return bool
	 */
	public function saveTag( $module, $tagId, $tag ) {

		$this->_checkModule( $module );
		$db = \Difra\MySQL::getInstance();

		$tagData = $this->_prepareTag( array( $tag ) );

		$query = "UPDATE `{$module}_tags` SET `tag` = " . $tagData[0] . ", `link`='" .
			 $db->escape( $this->_makeLink( $tagData[0] ) ) . "' WHERE `id`='" . intval( $tagId ) . "'";
		$db->query( $query );
		return true;
	}

	/**
	 * Удаляет тег
	 * @param $module
	 * @param $tagId
	 *
	 * @return bool
	 */
	public function deleteTag( $module, $tagId ) {

		$this->_checkModule( $module );
		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `{$module}_tags` WHERE `id`='" . intval( $tagId ) . "'" );
		return true;
	}

	/**
	 * Возвращает в xml все альясы имеющиеся в системе
	 *
	 * @param \DOMNode $node
	 */
	public function getAliasesXML( $node ) {

		$db  = \Difra\MySQL::getInstance();
		$res = $db->fetch( "SELECT * FROM `tags_aliases` ORDER BY `alias` ASC" );
		if( !empty( $res ) ) {
			$aliasesArray = array();

			// собираем массив с альясами
			foreach( $res as $data ) {
				$aliasesArray[$data['alias']][] = array( 'id' => $data['id'], 'tag' => $data['tag'] );
			}

			// собираем xml
			foreach( $aliasesArray as $alias => $tags ) {
				/** @var \DOMElement $aliasNode */
				$aliasNode = $node->appendChild( $node->ownerDocument->createElement( 'alias' ) );
				$aliasNode->setAttribute( 'name', $alias );
				foreach( $tags as $data ) {
					/** @var \DOMElement $tagNode */
					$tagNode = $aliasNode->appendChild( $node->ownerDocument->createElement( 'tag' ) );
					$tagNode->setAttribute( 'name', $data['tag'] );
					$tagNode->setAttribute( 'id', $data['id'] );
				}
			}
		}
	}

	/**
	 * Удаляет альяс у тега
	 * @param $aliasId
	 */
	public function deleteAlias( $aliasId ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `tags_aliases` WHERE `id`='" . intval( $aliasId ) . "'" );
	}
}
