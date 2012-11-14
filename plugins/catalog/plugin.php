<?php

namespace Difra\Plugins\Catalog;
class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'dispatch', '\Difra\Plugins\Catalog', 'addCategoryXML' );
	}

	public function getSitemap() {

		$urls = array();
		$urlPrefix = 'http://' . \Difra\Site::getInstance()->getHostname();
		$categories = Category::getList( true );
		if( !empty( $categories ) ) {
			foreach( $categories as $category ) {
				$urls[] = array(
					'loc' => $urlPrefix . $category->getFullLink()
				);
			}
		}
		$items = Item::getList( null, -1, 1, null, true );
		if( !empty( $items ) ) {
			foreach( $items as $item ) {
				$urls[] = array(
					'loc' => $urlPrefix . $item->getFullLink()
				);
			}
		}
		if( empty( $urls ) ) {
			return false;
		}
		return $urls;
	}
}
