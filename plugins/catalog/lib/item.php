<?php

namespace Difra\Plugins\Catalog;

use Difra\Envi\Session;

class Item {

	// основные данные
	private $id = null;
	/** @var int */
	private $category = null;
	private $name = '';
	private $visible = true;
	/** @var float|null */
	private $price = null;
	private $sale = null;
	private $shortdesc = '';
	private $description = '';
	private $link = '';

	// дата создания
	private $created = '';
	private $humanDate = '';

	// рабочие данные
	private $loaded = true;
	private $modified = false;
	private static $count = 0;

	// изображения
	private $images = array();
	private $imgSizes = array();

	// дополнительные поля
	private $ext = array();
	private $loadedExt = false;
	private $modifiedExt = false;

	// сортировка
	private static $sort = 'new';

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->imgSizes = self::getSizes();
	}

	/**
	 * Возвращает размеры, в которых хранятся изображения
	 * @static
	 * @return array|mixed
	 */
	static public function getSizes() {

		$sizes = \Difra\Config::getInstance()->getValue( 'catalog', 'imgSizes' );
		if( !$sizes or empty( $sizes ) ) {
			$sizes = array(
				's' => array( 70, 70 ),
				'm' => array( 150, 150 ),
				'l' => array( 370, 370 ),
			);
		}
		$sizes['f'] = false;
		return $sizes;
	}

	/**
	 * Создание нового элемента
	 * @static
	 * @return Item
	 */
	public static function create() {

		return new self;
	}

	/**
	 * Получение элемента по id
	 *
	 * @static
	 * @param int $id
	 * @return Item
	 */
	public static function get( $id ) {

		$item = new self;
		$item->id = $id;
		$item->loaded = false;
		return $item;
	}

	/**
	 * Загрузка данных элемента
	 * @return bool
	 */
	public function load() {

		if( $this->loaded ) {
			return true;
		}
		if( !$this->id ) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow( 'SELECT * FROM `catalog_items` WHERE `id`=\'' . $db->escape( $this->id ) . "'" );
		if( empty( $data ) ) {
			return false;
		}
		$this->name = $data['name'];
		$this->category = $data['category'];
		$this->visible = $data['visible'] ? true : false;
		$this->price = $data['price'] ? $data['price'] : null;
		$this->sale = $data['sale'] ? $data['sale'] : null;
		$this->shortdesc = $data['shortdesc'];
		$this->description = $data['description'];
		$this->link = $data['link'];
		$this->created = $data['created'];
		$this->humanDate = \Difra\Locales::getInstance()->getDateFromMysql( $data['created'] );
		$this->loaded = true;

		$db = \Difra\MySQL::getInstance();
		$imgData = $db->fetch( 'SELECT `id`,`main` FROM `catalog_images` WHERE `item`=\'' . $db->escape( $this->id ) . "'" );
		if( !empty( $imgData ) ) {
			foreach( $imgData as $img ) {
				$this->images[] = array(
					'main' => $img['main'] ? true : false,
					'id' => $img['id']
				);
			}
		}
		return true;
	}

	/**
	 * Загрузка значений дополнительных полей
	 * @return mixed
	 */
	public function loadExt() {

		if( $this->loadedExt ) {
			return;
		}
		$db = \Difra\MySQL::getInstance();
		$extData = $db->fetch(
			"SELECT `item`,`catalog_items_ext`.`ext` AS `ext_id`,`catalog_ext`.`name` AS `ext_name`," .
			"`catalog_ext`.`set` AS `ext_type`,`catalog_ext_sets`.`id` AS `set_id`,`catalog_ext_sets`.`name` AS `set_value`," .
			"`catalog_items_ext`.`value` AS `ext_value`," .
			"`catalog_ext`.`position` AS `ext_position`,`catalog_ext_sets`.`position` AS `set_position` " .
			"FROM `catalog_items_ext` LEFT JOIN `catalog_ext` ON `catalog_items_ext`.`ext`=`catalog_ext`.`id` " .
			"LEFT JOIN `catalog_ext_sets` ON `catalog_items_ext`.`setvalue`=`catalog_ext_sets`.`id` " .
			"WHERE `item`='" . $db->escape( $this->getId() ) . "' " .
			"ORDER BY `item`,`catalog_ext`.`position`,`catalog_ext_sets`.`position`"
		);
		if( !empty( $extData ) ) {
			foreach( $extData as $ext ) {
				if( $ext['ext_value'] ) {
					$this->ext[$ext['ext_id']] = $ext;
				} else {
					if( !isset( $this->ext[$ext['ext_id']] ) ) {
						$this->ext[$ext['ext_id']] = array();
					}
					$this->ext[$ext['ext_id']][$ext['set_id']] = $ext;
				}
			}
		}
		$this->loadedExt = true;
	}

	/**
	 * Возвращает данные дополнительных полей
	 * @return array
	 */
	public function getExt() {

		if( $this->modifiedExt ) {
			$this->saveExts();
		}
		$this->load();
		$this->loadExt();
		return $this->ext;
	}

	/**
	 * @static
	 * @param int|null|array $category
	 * @param bool           $withExt                -1 — без изображений, false — без расширенных полей, true — с расширенными полями
	 * @param int            $page
	 * @param int|null       $perPage
	 * @param null|bool      $visible
	 * @param bool           $withSubcategories
	 * @param bool|array     $ids
	 * @return Item[]|bool
	 */
	public static function getList( $category = null,
					$withExt = false,
					$page = 1,
					$perPage = null,
					$visible = null,
					$withSubcategories = false,
					$ids = null ) {

		if( $withSubcategories and !is_null( $category ) ) {
			return self::getList( Category::getSubtree( $category ),
				$withExt === true,
				$page,
				$perPage,
				$visible,
				false,
				$ids );
		}
		$db = \Difra\MySQL::getInstance();
		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM `catalog_items`';
		$where = array();
		if( is_array( $category ) ) {
			foreach( $category as $k => $v ) {
				$category[$k] = $db->escape( $v );
			}
			$where[] = "`category` IN ('" . implode( "','", $category ) . "')";
		} elseif( !is_null( $category ) ) {
			$where[] = "`category`='" . $db->escape( $category ) . "'";
		}
		if( !is_null( $visible ) ) {
			$where[] = "`visible`=" . ( $visible ? '1' : '0' );
		}
		if( $ids ) {
			foreach( $ids as $k => $v ) {
				$ids[$k] = $db->escape( $v );
			}
			$where[] = "`id` IN ('" . implode( "','", $ids ) . "')";
		}
		if( !empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		$sort = self::getSort();
		switch( $sort ) {
		case 'new':
			$query .= ' ORDER BY `id` DESC';
			break;
		case 'price':
			$query .= ' ORDER BY `price`';
			break;
		case null:
			$query .= " ORDER BY FIND_IN_SET( `id`, '" . implode( ',', $ids ) . "' )";
			break;
		}

		if( $perPage ) {
			$query .= " LIMIT " . intval( ( $page - 1 ) * $perPage ) . "," . intval( $perPage );
		}

		$data = $db->fetch( $query );
		self::$count = $db->getFoundRows();
		if( empty( $data ) ) {
			return false;
		}
		$res = array();
		$k = 1;
		$ids = array();

		foreach( $data as $i ) {
			$item = new self;
			$item->id = $i['id'];
			$item->name = $i['name'];
			$item->category = $i['category'];
			$item->visible = $i['visible'] ? true : false;
			$item->price = $i['price'] ? $i['price'] : null;
			$item->sale = $i['sale'] ? $i['sale'] : null;
			$item->shortdesc = $i['shortdesc'];
			$item->description = $i['description'];
			$item->link = $i['link'];
			$item->created = $i['created'];
			$item->humanDate = \Difra\Locales::getInstance()->getDateFromMysql( $i['created'] );
			if( $withExt ) {
				$item->loadedExt = true; // не правда, но чуть ниже получим
			}
			$res[$k] = $item;
			$ids[$k] = $db->escape( $i['id'] );
			$k++;
		}
		$keys = array_flip( $ids );
		// изображения
		if( $withExt == -1 ) {
			return $res;
		}
		$imgData =
			$db->fetch( "SELECT `id`,`item`,`main` FROM `catalog_images` WHERE `item` IN ('" . implode( "','", $ids ) . "')" );
		foreach( $imgData as $i ) {
			$res[$keys[$i['item']]]->images[] = array(
				'main' => $i['main'] ? true : false,
				'id' => $i['id']
			);
		}
		// exts
		if( !$withExt ) {
			return $res;
		}
		$extData = $db->fetch(
			"SELECT `item`,`catalog_items_ext`.`ext` AS `ext_id`,`catalog_ext`.`name` AS `ext_name`," .
			"`catalog_ext`.`set` AS `ext_type`,`catalog_ext_sets`.`id` AS `set_id`,`catalog_ext_sets`.`name` AS `set_value`," .
			"`catalog_items_ext`.`value` AS `ext_value`," .
			"`catalog_ext`.`position` AS `ext_position`,`catalog_ext_sets`.`position` AS `set_position`" .
			"FROM `catalog_items_ext` LEFT JOIN `catalog_ext` ON `catalog_items_ext`.`ext`=`catalog_ext`.`id` " .
			"LEFT JOIN `catalog_ext_sets` ON `catalog_items_ext`.`setvalue`=`catalog_ext_sets`.`id` " .
			"WHERE `item` IN ('" . implode( "','", $ids ) . "') " .
			"ORDER BY `item`"
		);
		if( !empty( $extData ) ) {
			foreach( $extData as $ext ) {
				if( $ext['ext_value'] ) {
					$res[$keys[$ext['item']]]->ext[$ext['ext_id']] = $ext;
				} else {
					if( !isset( $res[$keys[$ext['item']]]->ext[$ext['ext_id']] ) ) {
						$res[$keys[$ext['item']]]->ext[$ext['ext_id']] = array();
					}
					$res[$keys[$ext['item']]]->ext[$ext['ext_id']][$ext['set_id']] = $ext;
				}
			}
		}
		return $res;
	}

	/**
	 * Возвращает тип сортировки
	 * @static
	 * @return string
	 */
	static public function getSort() {

		if( isset( $_SESSION['catalog-sort'] ) ) {
			self::$sort = $_SESSION['catalog-sort'];
		}
		return self::$sort;
	}

	/**
	 * Установить тип сортировки
	 * @static
	 * @param $sort
	 */
	static public function setSort( $sort ) {

		Session::start();
		$_SESSION['catalog-sort'] = self::$sort = $sort;
	}

	/**
	 * Получить общее количество элементов, которые подходят под критерии поиска последнего запроса Item::getList()
	 *
	 * @static
	 * @return int
	 */
	static public function getCount() {

		return self::$count;
	}

	/**
	 * Сохраняет изменения
	 */
	private function save() {

		if( !$this->modified ) {
			return;
		}
		$db = \Difra\MySQL::getInstance();
		if( $this->id ) {
			$db->query( 'UPDATE `catalog_items` SET '
				. "`name`='" . $db->escape( $this->name ) . "',"
				. "`category`='" . $db->escape( $this->category ) . "',"
				. "`visible`=" . ( $this->visible ? '1' : '0' ) . ","
				. "`price`=" . ( $this->price ? "'" . $db->escape( $this->price ) . "'" : 'NULL' ) . ','
				. "`sale`=" . ( $this->sale ? "'" . $db->escape( $this->sale ) . "'" : 'NULL' ) . ','
				. "`link`='" . $db->escape( $this->link ) . "',"
				. "`shortdesc`='" . $db->escape( $this->shortdesc ) . "',"
				. "`description`='" . $db->escape( $this->description ) . "', "
				. "`modified`=NOW() "
				. "WHERE `id`='" . $db->escape( $this->id ) . "'"
			);
		} else {
			$db->query( 'INSERT INTO `catalog_items` SET '
				. "`name`='" . $db->escape( $this->name ) . "',"
				. "`category`='" . $db->escape( $this->category ) . "',"
				. "`visible`=" . ( $this->visible ? '1' : '0' ) . ","
				. "`price`=" . ( $this->price ? "'" . $db->escape( $this->price ) . "'" : 'NULL' ) . ','
				. "`sale`=" . ( $this->sale ? "'" . $db->escape( $this->sale ) . "'" : 'NULL' ) . ','
				. "`link`='" . $db->escape( $this->link ) . "',"
				. "`shortdesc`='" . $db->escape( $this->shortdesc ) . "',"
				. "`description`='" . $db->escape( $this->description ) . "'"
			);
			$this->id = $db->getLastId();
		}
		$this->modified = false;
	}

	/**
	 * Сохраняет изменения расширенных полей
	 */
	private function saveExts() {

		if( !$this->modifiedExt ) {
			return;
		}
		if( !$this->id ) {
			$this->save();
		}
		$db = \Difra\MySQL::getInstance();
		$itemId = $db->escape( $this->getId() );
		$queries = array();
		$queries[] = "DELETE FROM `catalog_items_ext` WHERE `item`='$itemId'";
		foreach( $this->ext as $id => $ext ) {
			$id = $db->escape( $id );
			if( is_array( $ext ) ) {
				// set
				foreach( $ext as $k => $v ) {
					$queries[] =
						'INSERT INTO `catalog_items_ext` SET ' .
						"`item`='$itemId'," .
						"`ext`='$id'," .
						"`setvalue`='" . $db->escape( $k ) . "'";
				}
			} else {
				// value
				$queries[] =
					'INSERT INTO `catalog_items_ext` SET ' .
					"`item`='$itemId'," .
					"`ext`='$id'," .
					"`value`='" . $db->escape( $ext ) . "'";
			}
		}
		$db->query( $queries );
		$this->modifiedExt = false;
	}

	/**
	 * Деструктор
	 */
	public function __destruct() {

		$this->save();
		$this->saveExts();
	}

	/**
	 * Возвращает id
	 *
	 * @return int
	 */
	public function getId() {

		if( !$this->id ) {
			$this->save();
		}
		return $this->id;
	}

	/**
	 * Устанавливает имя
	 * @param string $name
	 */
	public function setName( $name ) {

		$this->load();
		if( $this->name == $name ) {
			return;
		}
		$this->name = $name;
		$this->link = preg_replace( '/[^A-Za-z0-9А-Яа-я-]+/u', '_', $name );
		$this->modified = true;
	}

	/**
	 * Возвращает имя
	 * @return string
	 */
	public function getName() {

		$this->load();
		return $this->name;
	}

	/**
	 * Устанавливает категорию
	 * @param int $category
	 */
	public function setCategory( $category ) {

		$this->load();
		if( $this->category == $category ) {
			return;
		}
		$this->category = $category;
		$this->modified = true;
	}

	/**
	 * Возвращает id категории
	 *
	 * @return int
	 */
	public function getCategory() {

		$this->load();
		return $this->category;
	}

	/**
	 * Показывает/скрывает элемент
	 * @param bool $visible
	 */
	public function setVisible( $visible ) {

		$this->load();
		if( $visible instanceof \Difra\Param\AjaxCheckbox ) {
			$visible = $visible->val();
		}
		$visible = $visible ? true : false;
		if( $this->visible == $visible ) {
			return;
		}
		$this->visible = $visible;
		$this->modified = true;
	}

	/**
	 * Изменяет цену
	 * @param float $price
	 */
	public function setPrice( $price ) {

		$this->load();
		if( $price instanceof \Difra\Param\AjaxFloat ) {
			$price = $price->val();
		}
		$price = $price ? floatval( $price ) : null;
		if( $this->price == $price ) {
			return;
		}
		$this->price = $price;
		$this->modified = true;
	}

	/**
	 * Изменяет описание
	 * @param string|\Difra\Param\AjaxSafeHTML|\Difra\Param\AjaxHTML $description
	 */
	public function setDescription( $description ) {

		$this->load();
		if( method_exists( $description, 'saveImages' ) ) {
			if( $this->description == $description->val( true ) ) {
				return;
			}
			$this->id ? : $this->save();
			$description->saveImages( DIR_DATA . 'catalog/desc/' . $this->id, '/catalog/desc/' . $this->id );
			$this->description = $description->val();
		} else {
			$descTxt = method_exists( $description, 'val' ) ? $description->val() : $description;
			if( $this->description == $descTxt ) {
				return;
			}
		}
		$this->modified = true;
	}

	/**
	 * Установка новых значений расширенных полей
	 * @param array $data
	 */
	public function setExt( $data ) {

		$exts = array();
		if( $data instanceof \Difra\Param\AjaxData ) {
			$data = $data->val();
		}
		if( empty( $data ) ) {
			$this->ext = $exts;
			return;
		}
		foreach( $data as $extId => $ext ) {
			if( !$ext or ( !is_array( $ext ) and !trim( $ext ) ) ) {
				// no value
			} elseif( is_array( $ext ) ) {
				// set
				$a = array();
				foreach( $ext as $k => $v ) {
					if( $v ) {
						$a[$k] = 1;
					}
				}
				$exts[$extId] = $a;
			} else {
				// string
				$exts[$extId] = trim( $ext );
			}
		}
		$this->ext = $exts;
		$this->modifiedExt = true;
	}

	/**
	 * Заменяет все изображения
	 * @param string|\Difra\Param\AjaxFile                        $main
	 * @param string|\Difra\Param\AjaxFile|\Difra\param\AjaxFiles $more
	 */
	public function setImages( $main, $more = null ) {

		$this->cleanImages();

		if( !is_null( $main ) ) {
			$img = $main instanceof \Difra\Param\AjaxFile ? $main->val() : false;
			$this->addImage( $img, true );
		}

		if( !$more ) {
			return;
		} elseif( $more instanceof \Difra\Param\AjaxFile ) {
			$more = array( $more->val() );
		} elseif( $more instanceof \Difra\Param\AjaxFiles ) {
			$more = $more->val();
		} elseif( !is_array( $more ) ) {
			$more = array( $more );
		}
		foreach( $more as $img ) {
			$this->addImage( $img, false );
		}
	}

	/**
	 * Удаление всех изображений
	 */
	public function cleanImages() {

		$db = \Difra\MySQL::getInstance();
		if( !$this->id ) {
			return;
		}
		$images = $db->fetch( 'SELECT `id` FROM `catalog_images` WHERE `item`=\'' . $db->escape( $this->id ) . "'" );
		if( empty( $images ) ) {
			return;
		}
		foreach( $images as $imgId ) {
			foreach( $this->imgSizes as $k => $v ) {
				@unlink( DIR_DATA . 'catalog/items/' . $imgId['id'] . $k . '.png' );
			}
			$db->query( 'DELETE FROM `catalog_images` WHERE `id`=\'' . $db->escape( $imgId['id'] ) . "'" );
		}
		$this->images = array();
	}

	/**
	 * Добавление изображений
	 * @param string|\Difra\Param\AjaxFile|\Difra\Param\AjaxFiles $images
	 */
	public function addImages( $images ) {

		if( !$images ) {
			return;
		} elseif( $images instanceof \Difra\Param\AjaxFiles ) {
			$images = $images->val();
		} elseif( !is_array( $images ) ) {
			$images = array( $images );
		}
		foreach( $images as $img ) {
			$this->addImage( $img, false );
		}
	}

	/**
	 * Добавление изображения
	 * @param string|\Difra\Param\AjaxFile $image
	 * @param bool                         $main
	 * @throws \Difra\Exception
	 */
	public function addImage( $image, $main = false ) {

		$path = DIR_DATA . 'catalog/items/';
		@mkdir( $path, 0777, true );
		$this->save();
		$this->load();
		$useScaleAndCrop = \Difra\Config::getInstance()->getValue( 'catalog', 'usescale' );

		try {
			$rawImg = \Difra\Libs\Images::getInstance()->data2image( $image );
		} catch( \Difra\Exception $ex ) {
			throw new \Difra\Exception( 'Bad image format.' );
		}
		$db = \Difra\MySQL::getInstance();
		$db->query( 'INSERT INTO `catalog_images` SET '
			. "`item`='" . $db->escape( $this->id ) . "',"
			. "`main`=" . ( $main ? '1' : '0' )
		);
		$imgId = $db->getLastId();
		foreach( $this->imgSizes as $k => $size ) {
			if( $size ) {

				if( is_null( $useScaleAndCrop ) || intval( $useScaleAndCrop ) == 0 ) {
					$newImg = \Difra\Libs\Images::getInstance()->createThumbnail( $rawImg, $size[0], $size[1], 'png' );
				} else {
					$newImg = \Difra\Libs\Images::getInstance()->scaleAndCrop( $rawImg, $size[0], $size[1], 'png' );
				}

			} else {
				$newImg = \Difra\Libs\Images::getInstance()->convert( $rawImg, 'png' );
			}
			if( file_put_contents( $path . $imgId . $k . '.png', $newImg ) === false ) {
				throw new \Difra\Exception( 'Can\'t save image file.' );
			}
		}
	}

	/**
	 * Делает изображение главным
	 * @param int $id
	 */
	public function setMainImage( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( 'UPDATE `catalog_images` SET `main`=0 WHERE `item`=\'' . $db->escape( $this->id ) . "'" );
		$db->query( 'UPDATE `catalog_images` SET `main`=1 WHERE `item`=\'' . $db->escape( $this->id ) . "' AND `id`='"
		. $db->escape( $id ) . "'" );
	}

	/**
	 * Удаляет изображение
	 * @param int  $id
	 * @param bool $force
	 */
	public function deleteImage( $id, $force = false ) {

		$this->load();
		if( empty( $this->images ) ) {
			return;
		}
		foreach( $this->images as $k1 => $img ) {
			if( $img['id'] == $id and ( $force or $img['main'] == 0 ) ) {
				foreach( $this->imgSizes as $k2 => $v ) {
					@unlink( DIR_DATA . 'catalog/items/' . $id . $k2 . '.png' );
				}
				$db = \Difra\MySQL::getInstance();
				$db->query( 'DELETE FROM `catalog_images` WHERE `id`=\'' . $db->escape( $id ) . "'" );
				unset( $this->images[$k1] );
				return;
			}
		}
	}

	/**
	 * Возвращает данные о товаре в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public function getXML( $node ) {

		$this->load();
		$node->setAttribute( 'id', $this->getId() );
		$node->setAttribute( 'category', $this->category );
		$node->setAttribute( 'name', $this->name );
		$node->setAttribute( 'visible', $this->visible ? '1' : '0' );
		$node->setAttribute( 'price', $this->price );
		$node->setAttribute( 'humanprice', $this->price ? money_format( '%!n', $this->price ) : '' );
		$node->setAttribute( 'sale', $this->sale );
		$node->setAttribute( 'link', $this->getFullLink() );
		$node->setAttribute( 'shortdesc', $this->shortdesc );
		$node->setAttribute( 'description', $this->description );
		$node->setAttribute( 'created', $this->created );
		$node->setAttribute( 'humanDate', $this->humanDate );
		if( !empty( $this->images ) ) {
			foreach( $this->images as $img ) {
				/** @var \DOMElement $imgNode */
				$imgNode = $node->appendChild( $node->ownerDocument->createElement( 'image' ) );
				$imgNode->setAttribute( 'main', $img['main'] ? '1' : '0' );
				$imgNode->setAttribute( 'id', $img['id'] );
			}
		}
		if( $this->loadedExt ) {
			foreach( $this->ext as $ext ) {
				/** @var \DOMElement $extNode */
				$extNode = $node->appendChild( $node->ownerDocument->createElement( 'ext' ) );
				if( isset( $ext['item'] ) ) {
					$extNode->setAttribute( 'id', $ext['ext_id'] );
					$extNode->setAttribute( 'name', $ext['ext_name'] );
					$extNode->setAttribute( 'type', $ext['ext_type'] );
					$extNode->setAttribute( 'position', $ext['ext_position'] );
					$extNode->setAttribute( 'value', $ext['ext_value'] );
				} else {
					$extInit = false;
					foreach( $ext as $set ) {
						if( !$extInit ) {
							$extNode->setAttribute( 'id', $set['ext_id'] );
							$extNode->setAttribute( 'name', $set['ext_name'] );
							$extNode->setAttribute( 'type', $set['ext_type'] );
							$extNode->setAttribute( 'position', $set['ext_position'] );
							$extInit = true;
						}
						/** @var \DOMElement $setNode */
						$setNode = $extNode->appendChild( $extNode->ownerDocument->createElement( 'set' ) );
						$setNode->setAttribute( 'id', $set['set_id'] );
						$setNode->setAttribute( 'value', $set['set_value'] );
						$setNode->setAttribute( 'position', $set['set_position'] );
					}
				}
			}
		}
	}

	/**
	 * Возвращает ключевую часть URL
	 *
	 * @return string
	 */
	public function getLink() {

		return $this->link;
	}

	/**
	 * Возвращает полный URI к элементу
	 *
	 * @return string
	 */
	public function getFullLink() {

		return Category::get( $this->category )->getFullLink() . '/' . $this->id . '-' . $this->link;
	}

	/**
	 * Возвращает текущее описание
	 * @return string
	 */
	public function getDescription() {

		return $this->description;
	}

	/**
	 * Возвращает дату добавления элемента
	 * @return string
	 */
	public function getCreated() {

		return $this->created;
	}

	/**
	 * Удаляет элемент
	 */
	public function delete() {

		// TODO: проверять, есть ли такой id в заказах. если есть, перемещать в архив (класс ItemArchive extends Item).
		// При удалении заказа вызывать ItemArchive::getInstance($id)->delete(), где проверять,
		// используется ли ещё этот элемент и удалять только если элемент больше не используется.
		// Для этого надо упоминания таблицы catalog_items заменить на константу, чтобы в наследуемом классе сделать
		// catalog_archive
		$db = \Difra\MySQL::getInstance();
		$this->cleanImages();
		if( $this->id ) {
			@rmdir( DIR_DATA . 'catalog/items/' . $this->id );
			$path = DIR_DATA . 'catalog/desc/' . $this->id;
			if( is_dir( $path ) ) {
				$dir = opendir( $path );
				while( false !== ( $file = readdir( $dir ) ) ) {
					if( $file{0} == '.' ) {
						continue;
					}
					@unlink( "$path/$file" );
				}
				@rmdir( $path );
			}
			$db->query( "DELETE FROM `catalog_items` WHERE `id`='" . $db->escape( $this->id ) . "'" );
		}
		$this->modified = $this->modifiedExt = false;
	}
}