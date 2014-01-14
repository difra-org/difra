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
		\Difra\Param\AjaxData $roles = null,
		\Difra\Param\AjaxFiles $image = null
	) {

		if( $id ) {
			$entry = \Difra\Unify::getObj( 'PortfolioEntry', (string)$id );
		} else {
			$entry = \Difra\Unify::createObj( 'PortfolioEntry' );
		}
		$entry->name = $name;
		if( !is_null( $release ) ) {
			$release = strtotime( $release->val() . ' 00:00:00' );
			$release = date( 'Y-m-d', $release );
		}
		// $entry->description = $description;
		$entry->release = $release;
		$entry->link = $link;
		$entry->link_caption = $link_caption;
		$entry->software = $software;
		$entry->uri = \Difra\Locales::getInstance()->makeLink( $name->val() );

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

		try{
			$entry->save();
		} catch( \Difra\Exception $x ) {
			$x->notify();
			$this->ajax->notify( $x->getMessage() );
			return;
		}
		if( !is_null( $image ) ) {

			if( $id ) {
				$eId = $id->val();
			} else {
				$eId = $entry->getPrimaryValue();
			}

			try{
				\Difra\Plugins\Portfolio::saveImages( $eId, $image );
			} catch( \Difra\Exception $x ) {
				$x->notify();
				$this->ajax->notify( $x->getMessage() );
				return;
			}
		}

		$Locales = \Difra\Locales::getInstance();
		if( $id ) {
			$notify = $Locales->getXPath( 'portfolio/adm/notify/edited' );
		} else {
			$notify = $Locales->getXPath( 'portfolio/adm/notify/added' );
		}

		$this->ajax->notify( $notify );
		$this->ajax->redirect( '/adm/content/portfolio/' );
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		$entry = \Difra\Unify::getObj( 'PortfolioEntry', $id->val() );

		$images = new \Difra\Unify\Search( 'PortfolioImages' );
		$images->addCondition( 'portfolio', $id->val() );
		$imageList = $images->getList();

		if( !empty( $imageList ) ) {
			foreach( $imageList as $img ) {
				\Difra\Plugins\Portfolio::deleteImage( $img->id );
			}
		}

		$entry->delete();
		$this->ajax->refresh();
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioEntryEdit' ) );
		$mainXml->setAttribute( 'edit', true );
		$entryNode = $mainXml->appendChild( $this->xml->createElement( 'entry' ) );
		$entry = \Difra\Unify::getObj( 'PortfolioEntry', $id->val() );
		$entry->getXML( $entryNode );

		$imagesNode = $entryNode->appendChild( $this->xml->createElement( 'images' ) );

		$images = new \Difra\Unify\Search( 'PortfolioImages' );
		$images->addCondition( 'portfolio', $entry->id );
		$images->setOrder( array( 'position' ) );
		$images->getListXML( $imagesNode );
	}

	public function imagedownAjaxAction( \Difra\Param\AnyInt $id ) {

		try{
			\Difra\Plugins\Portfolio::imageDown( $id->val() );
		} catch( \Difra\Exception $x ) {
			$x->notify();
			$this->ajax->notify( $x->getMessage() );
			return;
		}
		$this->ajax->refresh();
	}

	public function imageupAjaxAction( \Difra\Param\AnyInt $id ) {

		try{
			\Difra\Plugins\Portfolio::imageUp( $id->val() );
		} catch( \Difra\Exception $x ) {
			$x->notify();
			$this->ajax->notify( $x->getMessage() );
			return;
		}
		$this->ajax->refresh();
	}

	public function deleteimageAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Portfolio::deleteImage( $id->val() );
		$this->ajax->refresh();
	}

}