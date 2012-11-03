<?php

namespace Difra\Plugins;

class Catalog {

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

	}

	/**
	 *
	 * Элементы каталога
	 *
	 */

	/**
	 * Добавление нового элемента каталога
	 * @param string                      $name
	 * @param int                         $category
	 * @param bool                        $visible
	 * @param float                       $price
	 * @param string                      $description
	 * @param array                       $ext
	 * @param \Difra\Param\AjaxFile       $mainImage
	 * @param \Difra\Param\AjaxFiles|null $images
	 */
	public function addItem( $name, $category, $visible, $price, $description, $ext, $mainImage, $images ) {

		$item = \Difra\Plugins\Catalog\Item::create();
		$item->setName( $name );
		$item->setCategory( $category );
		$item->setVisible( $visible );
		$item->setPrice( $price );
		$item->setDescription( $description );
		$item->setExt( $ext );
		$item->setImages( $mainImage, $images );
	}

	/**
	 * Редактирование элемента
	 * @param int    $id
	 * @param string $name
	 * @param int    $category
	 * @param bool   $visible
	 * @param float  $price
	 * @param string $description
	 * @param array  $ext
	 */
	public function updateItem( $id, $name, $category, $visible, $price, $description, $ext ) {

		$item = \Difra\Plugins\Catalog\Item::get( $id );
		$item->setName( $name );
		$item->setCategory( $category );
		$item->setVisible( $visible );
		$item->setPrice( $price );
		$item->setDescription( $description );
		$item->setExt( $ext );
	}

	public function deleteItem( $id ) {

		if( $item = \Difra\Plugins\Catalog\Item::get( $id ) ) {
			$item->delete();
		}
	}

	public function addImages( $id, $images ) {

		$item = \Difra\Plugins\Catalog\Item::get( $id );
		$item->addImages( $images );
	}

	public function deleteImage( $itemId, $imageId ) {

		$item = \Difra\Plugins\Catalog\Item::get( $itemId );
		$item->deleteImage( $imageId );
	}

	public function setMainImage( $itemId, $imageId ) {

		$item = \Difra\Plugins\Catalog\Item::get( $itemId );
		$item->setMainImage( $imageId );
	}

	/**
	 * Получение списка элементов каталога
	 * @param \DOMNode|null	$node
	 * @param int|null	$category
	 * @param bool		$withExt
	 * @param int|null	$page
	 * @param int|null	$perPage
	 * @param bool|null	$visible
	 * @param array|bool    $withSubcategories
	 *
	 * @return mixed
	 */
	public function getItemsXML( $node, $category = null, $withExt = false, $page = 1, $perPage = null, $visible = null,
				     $withSubcategories = false ) {

		if( $withSubcategories ) {
			\Difra\Plugins\Catalog\Category::getList( $visible ); // init category list
		}
		$list = \Difra\Plugins\Catalog\Item::getList( $category, $withExt, $page, $perPage, $visible, $withSubcategories );
		$this->_items2xml( $node, $list );
		return $list;
	}

	/**
	 * Возвращает количество элементов каталога, найденных во время последнего запроса к getItemsXML()
	 * @return int
	 */
	public function getItemsCount() {

		return \Difra\Plugins\Catalog\Item::getCount();
	}

	/**
	 * Возвращает список элементов по id в XML
	 *
	 * @param \DOMNode|null $node
	 * @param array         $ids
	 * @param int           $page
	 * @param int|null      $perPage
	 *
	 * @return array|bool
	 */
	public function getItemsById( $node, $ids, $page = 1, $perPage = null, $categoryId = null ) {

		$list = \Difra\Plugins\Catalog\Item::getList( $categoryId, true, $page, $perPage, true, true, $ids );
		$this->_items2xml( $node, $list );
		return $list;
	}

	/**
	 * Заполняет XML данными из массива элементов каталога
	 *
	 * @param $node
	 * @param $list
	 */
	private function _items2xml( $node, $list ) {

		if( $node and !empty( $list ) ) {
			foreach( $list as $item ) {
				$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
				$item->getXML( $itemNode );
			}
		}
	}

	/**
	 * Меняет режим сортировки
	 * @param string $sort
	 */
	public function setSort( $sort ) {

		\Difra\Plugins\Catalog\Item::setSort( $sort );
	}

	/**
	 * Возвращает тип сортировки
	 * @return string
	 */
	public function getSort() {

		return \Difra\Plugins\Catalog\Item::getSort();
	}

	/**
	 *
	 * Категории
	 *
	 */

	/**
	 * Возвращает список категорий в XML
	 *
	 * @param \DOMNode  $node
	 * @param bool|null $visible
	 * @param int|null  $selected
	 *
	 * @return bool
	 */
	public function getCategoriesListXML( $node, $visible = null, $selected = null ) {

		$list = \Difra\Plugins\Catalog\Category::getList( $visible );
		if( empty( $list ) ) {
			return false;
		}

		$selectedList = array();
		$cont = true;
		while( $selected and $cont ) {
			$cont = false;
			foreach( $list as $cat ) {
				if( $cat->getId() == $selected ) {
					$selectedList[] = $selected;
					$selected = $cat->getParent();
					$cont = true;
					break;
				}
			}
		}

		foreach( $list as $cat ) {
			$catNode = $node->appendChild( $node->ownerDocument->createElement( 'category' ) );
			$cat->getXML( $catNode );
			if( !empty( $selectedList ) and in_array( $cat->getId(), $selectedList ) ) {
				$catNode->setAttribute( 'selected', '1' );
			}
		}
		return true;
	}

	/**
	 * Добавление категории
	 * @param string   $name
	 * @param int|null $parent
	 */
	public function addCategory( $name, $parent = null ) {

		$category = \Difra\Plugins\Catalog\Category::create();
		$category->setName( $name );
		$category->setParent( $parent );
	}

	/**
	 * Редактирование категории
	 * @param int      $id
	 * @param string   $name
	 * @param int|null $parent
	 *
	 * @return bool
	 */
	public function updateCategory( $id, $name, $parent = null ) {

		$category = \Difra\Plugins\Catalog\Category::get( $id );
		if( !$category ) {
			return false;
		}
		$category->setName( $name );
		$category->setParent( $parent );
		return true;
	}

	/**
	 * Удаление категории
	 * @param int $id
	 */
	public function deleteCategory( $id ) {

		\Difra\Plugins\Catalog\Category::get( $id )->delete();
	}

	/**
	 * Переместить категорию «выше»
	 * @param int $id
	 */
	public function moveCategoryUp( $id ) {

		\Difra\Plugins\Catalog\Category::get( $id )->moveUp();
	}

	/**
	 * Переместить категорию «ниже»
	 * @param int $id
	 */
	public function moveCategoryDown( $id ) {

		\Difra\Plugins\Catalog\Category::get( $id )->moveDown();
	}

	/**
	 * Добавляет список категорий в XML (для меню)
	 */
	public function addCategoryXML() {

		$controller = \Difra\Action::getInstance()->controller;
		$catalogNode = $controller->root->appendChild( $controller->xml->createElement( 'catalogCategories' ) );
		$catalogNode->setAttribute( 'autorender', '0' );
		$this->getCategoriesListXML( $catalogNode, true, $this->getSelectedCategory() );
	}

	/**
	 *
	 * Выбранная категория
	 *
	 */

	private $selectedCategory = 0;

	public function setSelectedCategory( $id ) {

		$this->selectedCategory = $id;
	}

	public function getSelectedCategory() {

		return $this->selectedCategory;
	}

	/**
	 *
	 * Расширенные поля
	 *
	 */

	/**
	 * Добавление расширенного поля
	 * @param string $name
	 * @param int $set
	 * @param int|null $group
	 * @return Catalog\Ext
	 */
	public function addExt( $name, $set, $group = null ) {

		$ext = \Difra\Plugins\Catalog\Ext::create();
		$ext->setName( $name );
		$ext->setSet( $set );
		$ext->setGroup( $group );
		return $ext;
	}

	/**
	 * Изменение расширенного поля
	 * @param int $id
	 * @param string $name
	 * @param int $set
	 * @param int|null $group
	 * @return Catalog\Ext|null
	 */
	public function editExt( $id, $name, $set, $group = null ) {

		$ext = \Difra\Plugins\Catalog\Ext::get( $id );
		if( !$ext ) {
			return null;
		}
		$ext->setName( $name );
		$ext->setSet( $set );
		$ext->setGroup( $group );
		return $ext;
	}

}
