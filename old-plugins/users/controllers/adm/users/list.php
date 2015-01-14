<?php

use Difra\Plugins, Difra\Param;

class AdmUsersListController extends Difra\Controller {

	public function dispatch() {
		\Difra\View::$instance = 'adm';
	}

	public function indexAction( Param\NamedInt $page = null ) {

		$page = !is_null( $page ) ? $page->val() : 1;

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'users-list' ) );

		$Filter = new Plugins\Users\Filter();
		$Filter->getFilterXML( $mainXml );
		$Filter->getSortOrderXML( $mainXml );

		Plugins\Users::getListXML( $mainXml, $page );
	}

	public function editAction( Param\AnyInt $id ) {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'user-edit' ) );
		$userXml = $mainXml->appendChild( $this->xml->createElement( 'user' ) );
		$User = Plugins\Users\User::getById( $id->val() );
		if( !$User ) {
			throw new \Difra\View\Exception( 404 );
		}
		$User->getXML( $userXml );
	}

	public function banAjaxAction( Param\AnyInt $id ) {

		Plugins\Users\User::save( $id->val(), array( 'banned' => 1 ) );
		$this->ajax->refresh();
	}

	public function unbanAjaxAction( Param\AnyInt $id ) {

		Plugins\Users\User::save( $id->val(), array( 'banned' => 0 ) );
		$this->ajax->refresh();
	}

	public function activateAjaxAction( Param\AnyInt $id ) {

		Plugins\Users\User::save( $id->val(), array( 'active' => 1, 'activation' => '' ) );
		Plugins\Users\Libs\Notify::sendManualActivated( $id->val() );
		$this->ajax->refresh();
	}

	public function moderatorAjaxAction( Param\AnyInt $id ) {

		Plugins\Users\User::save( $id->val(), array( 'moderator' => 1 ) );
		$this->ajax->refresh();
	}

	public function unmoderatorAjaxAction( Param\AnyInt $id ) {

		Plugins\Users\User::save( $id->val(), array( 'moderator' => 0 ) );
		$this->ajax->refresh();
	}

	public function saveuserAjaxAction( Param\AjaxEmail $email, Param\AjaxCheckbox $active,
					    Param\AjaxCheckbox $banned, Param\AjaxCheckbox $moderator,
					    Param\AjaxCheckbox $change_pw = null, Param\AjaxString $password = null,
					    Param\AnyInt $id = null,

					    Param\AjaxData $fieldName = null, Param\AjaxData $fieldValue = null ) {

		$fields = array( 'email' => $email->val(), 'active' => $active->val(), 'banned' => $banned->val(), 'moderator' => $moderator );
		$id = !is_null( $id ) ? $id->val() : null;

		if( !is_null( $id ) ) {
			if( $change_pw->val() == 1 && !is_null( $password ) ) {
				$fields['password'] = md5( $password->val() );
				$fields['passwordChanged'] = date( 'Y-m-d H:i:s', time() );
			}
		}

		if( is_null( $id ) ) {

			$flagsArray = array( 'banned' => $banned->val(), 'active' => $active->val(), 'moderator' => $moderator->val() );
			try{
				$password = !is_null( $password ) ? $password->val() : null;
				$id = Plugins\Users\User::create( $email->val(), $password, $password, $flagsArray );

			} catch( Plugins\Users\userException $ex ) {
				return;
			} catch( \Difra\Exception $ex ) {
				$this->ajax->notify( $ex );
				return;
			}

		} else {
			Plugins\Users\User::save( $id, $fields );
		}

		// сохранение дополнительных полей
		$fieldName = !is_null( $fieldName ) ? $fieldName->val() : null;
		$fieldValue = !is_null( $fieldValue ) ? $fieldValue->val() : null;

		$additionalsArray = array( 'names' => $fieldName, 'values' => $fieldValue );
		Plugins\Users\Additionals::save( $id, $additionalsArray );

		if( !is_null( $id ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'users/adm/edit/userSaved' ) );
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'users/adm/add/added' ) );
		}

		$this->ajax->redirect( '/adm/users/list/' );
	}

	public function viewAjaxAction( Param\AnyInt $id ) {

		$xml = new DOMDocument();
		$rootNode = $xml->appendChild( $xml->createElement( 'root' ) );
		$mainNode = $rootNode->appendChild( $xml->createElement( 'user-view' ) );

		\Difra\Locales::getInstance()->getLocaleXML( $rootNode );

		$userNode = $mainNode->appendChild( $xml->createElement( 'user' ) );

		$User = Plugins\Users\User::getById( $id->val(), true );
		$User->getXML( $userNode );
		$this->ajax->display( \Difra\View::render( $xml, 'windows', true ) );
	}

	public function addAction() {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'user-edit' ) );
		$mainXml->setAttribute( 'new', true );
	}

}
