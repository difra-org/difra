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
		\Difra\Param\AjaxHTML $description,
		\Difra\Param\AjaxString $release = null,
		\Difra\Param\AjaxString $link = null,
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
		$entry->release = $release;
		$entry->link = $link;
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
	}
}