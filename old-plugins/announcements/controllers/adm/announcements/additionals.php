<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsAdditionalsController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$addNode = $this->root->appendChild( $this->xml->createElement( 'announcementsAdditionals' ) );
		\Difra\Plugins\Announcements\Additionals::getListXML( $addNode );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxString $alias,
					\Difra\Param\AjaxInt $id = null, \Difra\Param\AjaxString $originalAlias = null ) {

		$id = ! is_null( $id ) ? $id->val() : null;

		if( is_null( $id ) || $originalAlias->val() != $alias->val() ) {
			if( \Difra\Plugins\Announcements\Additionals::checkAlias( $alias->val() ) ) {
				return $this->ajax->invalid( 'alias', \Difra\Locales::getInstance()->getXPath( 'announcements/adm/additionals/duplicateName' ) );
			}
		}

		\Difra\Plugins\Announcements::getInstance()->saveAdditionalField( $name->val(), $alias->val(), $id );

		if( is_null( $id ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/additionals/added' ) );
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/additionals/updated' ) );
		}
		$this->ajax->refresh();
	}

	public function deleteAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Announcements\Additionals::delete( $id->val() );
		$this->ajax->refresh();
	}

}