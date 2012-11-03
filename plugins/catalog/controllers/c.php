<?php

class CController extends \Difra\Controller {

	public function indexAction() {

		$catId = 0;
		$nextCat = null;
		$linkparts = $this->action->parameters;
		$title = array();
		while( !empty( $this->action->parameters ) ) {
			$next = rawurldecode( $this->action->parameters[0] );
			if( $nextCat = \Difra\Plugins\Catalog\Category::getByLink( $next, $catId ) ) {
				array_shift( $this->action->parameters );
				$catId = $nextCat->getId();
				$title[] = $nextCat->getName();
				continue;
			}
			break;
		}
		\Difra\Plugins\Catalog::getInstance()->setSelectedCategory( $catId );
		// get page
		if(
			sizeof( $this->action->parameters ) >= 2
			and $this->action->parameters[sizeof( $this->action->parameters ) - 2] == 'page'
			and is_numeric( $this->action->parameters[sizeof( $this->action->parameters ) - 1] )
		) {
			$page = array_pop( $this->action->parameters );
			array_pop( $this->action->parameters );
			array_pop( $linkparts );
			array_pop( $linkparts );
		} else {
			$page = 1;
		}
		$action = 'view';
		switch( sizeof( $this->action->parameters ) ) {
		case 0:
			\Difra\Plugins\Catalog\View::getInstance()->viewCategory( $this, $catId, $page, $linkparts, $title );
			break;
		case 2:
			$action = array_pop( $this->action->parameters );
		case 1:
			\Difra\Plugins\Catalog\View::getInstance()->viewItem( $this, $this->action->parameters[0], $action, $title );
			$this->action->parameters = array();
			break;
		}
	}

	public function sortAjaxAction( \Difra\Param\AnyString $sort ) {

		\Difra\Plugins\Catalog::getInstance()->setSort( $sort->val() );
		$this->ajax->refresh();
	}
}