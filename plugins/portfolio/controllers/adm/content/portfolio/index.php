<?php

class AdmContentPortfolioIndexController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$search = new \Difra\Unify\Search( 'PortfolioEntry' );
		$search->getListXML( $this->root );
	}

	public function addAction() {

		$this->root->appendChild( $this->xml->createElement( 'PortfolioEntryAdd' ) );
	}

	public function saveAjaxAction(
		\Difra\Param\AjaxString $name,
		\Difra\Param\AjaxSafeHTML $description,
		\Difra\Param\AjaxString $release = null,
		\Difra\Param\AjaxString $link = null,
		\Difra\Param\AjaxString $link_caption = null,
		\Difra\Param\AjaxString $software = null,
		\Difra\Param\AjaxInt $id = null,
		\Difra\Param\AjaxData $roles = null
	) {

		if( $id ) {
			$entry = \Difra\Unify::getObj( 'PortfolioEntry', (string)$id );
		} else {
			$entry = \Difra\Unify::createObj( 'PortfolioEntry' );
		}
		$entry->name = $name;
		$entry->description = $description;
		if( !is_null( $release ) ) {
			$release = strtotime( $release->val() );
			$release = date( 'Y-m-d', $release );
		}
		$entry->release = $release;
		$entry->link = $link;
		$entry->link_caption = $link_caption;
		$entry->software = $software;

		$sortedAuthors = array();
		if( !is_null( $roles ) ) {
			$authors = $roles->val();
			if( !empty( $authors ) ) {
				foreach( $authors as $line ) {
					if( empty( $line ) ) {
						continue;
					}
					$role = false;
					$contributors = array();
					foreach( $line as $k => $v ) {
						if( $k === 'role' ) {
							$role = $v;
						} else {
							$contributors[] = $v;
						}
					}
					if( $role and !empty( $contributors ) ) {
						$sortedAuthors[] = array( 'role' => $role, 'contibutors' => $contributors );
					}
				}
			}
		}

		$entry->authors = $sortedAuthors;
		//$entry->save();
		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'portfolio/adm/notify/added' ) );
		$this->ajax->redirect( '/adm/content/portfolio/' );
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		//TODO: добавить удаление изображений

		$entry = \Difra\Unify::getObj( 'PortfolioEntry', $id->val() );
		$entry->delete();

		$this->ajax->refresh();
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioEntryEdit' ) );
		$mainXml->setAttribute( 'edit', true );
		$entryNode = $mainXml->appendChild( $this->xml->createElement( 'entry' ) );
		$entry = \Difra\Unify::getObj( 'PortfolioEntry', $id->val() );
		$entry->getXML( $entryNode );
	}


}