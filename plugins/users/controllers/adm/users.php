<?php

use Difra\Plugins, Difra\Param;

class AdmUsersController extends Difra\Controller {

	/** @var \DOMElement */
	private $node = null;

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction( \Difra\Param\NamedInt $page = null ) {

		$page = $page ? $page->val() : 1;
		$this->node = $this->root->appendChild( $this->xml->createElement( 'userList' ) );
		$this->node->setAttribute( 'link', '/adm/users' );
		$this->node->setAttribute( 'current', $page );
		Plugins\Users::getInstance()->getListXML( $this->node, $page );
	}

	public function editAction() {

		if( sizeof( $this->action->parameters ) != 1 ) {
			return;
		}
		$id = array_shift( $this->action->parameters );
		$this->node = $this->root->appendChild( $this->xml->createElement( 'userEdit' ) );
		Plugins\Users::getInstance()->getUserXML( $this->node, $id );

		$additionals = Difra\Additionals::getAdditionals( 'users', $id );
		if( $additionals ) {
			$additionalsNode = $this->node->appendChild( $this->xml->createElement( 'additionals' ) );
			foreach( $additionals as $key=>$value ) {
				$itemNode = $additionalsNode->appendChild( $this->xml->createElement( 'item' ) );
				$itemNode->setAttribute( 'name', $key );
				$itemNode->setAttribute( 'value', $value );
			}
		}
	}
	
	public function saveAction() {

		if( sizeof( $this->action->parameters ) != 1 ) {
			return;
		}
		$id = array_shift( $this->action->parameters );
		Plugins\Users::getInstance()->setUserLogin( $id, $_POST );

		if( isset( $_POST['additional_name'] ) && isset( $_POST['additional_value'] ) &&
			!empty( $_POST['additional_value'] ) && !empty( $_POST['additional_name'] ) ) {

			$additionals = array();
			foreach( $_POST['additional_name'] as $k=>$value ) {
				if( $value!='' && $_POST['additional_value'][$k]!='') {
					$additionals[$value] = $_POST['additional_value'][$k];
				}
			}
			
			Difra\Additionals::saveAllAdditionals( 'users', $id, $additionals );
		}
		$this->view->redirect( '/adm/users' );
	}

	public function banAction( Param\AnyInt $id ) {
		\Difra\Plugins\Users::getInstance()->ban( $id->val() );
		$this->view->redirect( '/adm/users/' );
	}

	public function unbanAction( Param\AnyInt $id ) {
		\Difra\Plugins\Users::getInstance()->unban( $id->val() );
		$this->view->redirect( '/adm/users/' );
	}

	public function moderatorAction( Param\AnyInt $id ) {
		\Difra\Plugins\Users::getInstance()->setModerator( $id->val() );
		$this->view->redirect( '/adm/users/' );
	}

	public function unmoderatorAction( Param\AnyInt $id ) {
		\Difra\Plugins\Users::getInstance()->unSetModerator( $id->val() );
		$this->view->redirect( '/adm/users/' );
	}
}

