<?php

class CController extends \Difra\Controller {

	public function indexAction() {

		$catId = 0;
		$nextCat = null;
		$linkparts = self::$parameters;
		$title = array();
		while( !empty( self::$parameters ) ) {
			$next = rawurldecode( self::$parameters[0] );
			if( $nextCat = \Difra\Plugins\Catalog\Category::getByLink( $next, $catId ) ) {
				array_shift( self::$parameters );
				$catId = $nextCat->getId();
				$title[] = $nextCat->getName();
				continue;
			}
			break;
		}
		\Difra\Plugins\Catalog::getInstance()->setSelectedCategory( $catId );
		// get page
		if(
			sizeof( self::$parameters ) >= 2
			and self::$parameters[sizeof( self::$parameters ) - 2] == 'page'
			and is_numeric( self::$parameters[sizeof( self::$parameters ) - 1] )
		) {
			$page = array_pop( self::$parameters );
			array_pop( self::$parameters );
			array_pop( $linkparts );
			array_pop( $linkparts );
		} else {
			$page = 1;
		}
		$action = 'view';
		switch( sizeof( self::$parameters ) ) {
		case 0:
			\Difra\Plugins\Catalog\View::getInstance()->viewCategory( $this, $catId, $page, $linkparts, $title );
			break;
		case 2:
			$action = array_pop( self::$parameters );
		case 1:
			\Difra\Plugins\Catalog\View::getInstance()->viewItem( $this, self::$parameters[0], $action, $title );
			self::$parameters = array();
			break;
		}
	}

	public function sortAjaxAction( \Difra\Param\AnyString $sort ) {

		\Difra\Plugins\Catalog::getInstance()->setSort( $sort->val() );
		$this->ajax->refresh();
	}
}