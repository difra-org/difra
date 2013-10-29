<?php

namespace Difra\Plugins\Catalog;

class View {

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	/**
	 * @param \Difra\Controller $controller
	 * @param int               $catId
	 * @param int               $page
	 * @param array             $linkparts
	 * @param bool|string       $title
	 * @param int               $cperpage
	 *
	 * @return mixed
	 */
	public function viewCategory( &$controller, $catId, $page, $linkparts, $title = false, $cperpage = 0 ) {

		if( !$cperpage ) {
			if( !$perpage = \Difra\Config::getInstance()->getValue( 'catalog', 'perpage' ) ) {
				$perpage = 20;
			}
		} else {
			$perpage = $cperpage;
		}

		$catalogNode = $controller->root->appendChild( $controller->xml->createElement( 'CatalogList' ) );
		$catalog = \Difra\Plugins\Catalog::getInstance();
		$catalogNode->setAttribute( 'sort', $sort = $catalog->getSort() );
		$baseLink = $linkparts ? '/c/' . implode( '/', $linkparts ) : '/c';
		$list = $catalog->getItemsXML( $catalogNode, $catId, true, $page, $perpage, true, true );

		if( !$cperpage ) {
			$pages = floor( ( \Difra\Plugins\Catalog::getInstance()->getItemsCount() - 1 ) / $perpage ) + 1;
			$catalogNode->setAttribute( 'pages', $pages );
			$catalogNode->setAttribute( 'current', $page );
			$catalogNode->setAttribute( 'link',  $baseLink );
		}
		if( $title ) {
			$title = implode( ' → ', $title );
			$controller->root->setAttribute( 'pageTitle', $title );
		}
		return $list;
	}

	public function viewItem( &$controller, $link, $action, $title ) {

		$itemNode = $controller->root->appendChild( $controller->xml->createElement( 'CatalogItem' ) );
		$link     = explode( '-', $link, 2 );
		if( sizeof( $link ) < 2 or !is_numeric( $link[0] ) ) {
			throw new \Difra\View\Exception(404);
			return;
		}
		$item = \Difra\Plugins\Catalog\Item::get( $link[0] );
		if( !$item->load() ) {
			throw new \Difra\View\Exception(404);
			return;
		}
		if( rawurldecode( $link[1] ) != $item->getLink() ) {
			throw new \Difra\View\Exception(404);
			return;
		}
		$item->loadExt();
		$item->getXML( $itemNode );
		$title[] = $item->getName();
		$title   = implode( ' → ', $title );
		$controller->root->setAttribute( 'pageTitle', $title );
	}
}