<?php

use Difra\Plugins, Difra\Param;

class AdmUsersListController extends Difra\Controller {

	/** @var \DOMElement */
	private $node = null;

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction( \Difra\Param\NamedInt $page = null ) {

		$page = $page ? $page->val() : 1;
		$this->node = $this->root->appendChild( $this->xml->createElement( 'userList' ) );
		$this->node->setAttribute( 'link', '/adm/users/list' );
		$this->node->setAttribute( 'current', $page );
		Plugins\Users::getInstance()->getListXML( $this->node, $page );
	}

	public function editAction( Param\AnyInt $id ) {

		$this->node = $this->root->appendChild( $this->xml->createElement( 'userEdit' ) );
		Plugins\Users::getInstance()->getUserXML( $this->node, $id->val() );
	}

	public function saveAjaxAction( Param\AnyInt $id, Param\AjaxEmail $email, Param\AjaxCheckbox $change_pw,
					Param\AjaxString $new_pw = null, Param\AjaxData $fieldName = null, Param\AjaxData $fieldValue = null ) {

		$userData = array( 'email' => $email->val(), 'change_pw' => $change_pw->val() );
		$userData['new_pw'] = !is_null( $new_pw ) ? $new_pw->val() : null;

		$userData['addonFields'] = !is_null( $fieldName ) ? $fieldName->val() : null;
		$userData['addonValues'] = !is_null( $fieldValue ) ? $fieldValue->val() : null;

		Plugins\Users::getInstance()->setUserLogin( $id->val(), $userData );

		if( $userData['change_pw']!=0 && !is_null( $userData['new_pw'] ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'auth/adm/userDataSavedPassChanged' ) );
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'auth/adm/userDataSaved' ) );
		}
		$this->ajax->refresh();
	}

	public function banAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Users::getInstance()->ban( $id->val() );
		$this->ajax->refresh();
	}

	public function unbanAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Users::getInstance()->unban( $id->val() );
		$this->ajax->refresh();
	}

	public function moderatorAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Users::getInstance()->setModerator( $id->val() );
		$this->ajax->refresh();
	}

	public function unmoderatorAjaxAction( Param\AnyInt $id ) {

		\Difra\Plugins\Users::getInstance()->unSetModerator( $id->val() );
		$this->ajax->refresh();
	}

	public function activateAjaxAction( Param\AnyInt $id ) {

		Plugins\Users::getInstance()->manualActivation( $id->val() );
		$this->ajax->refresh();
	}

}

