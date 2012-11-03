<?php

use Difra\Plugins;

class AdmPortfolioController extends Difra\Controller {

	public function dispatch() {
		$this->view->instance = 'adm';
	}

	public function indexAction() {
		
		$this->root->appendChild( $this->xml->createElement( 'portfolio-view' ) );
		$Portfolio = Difra\Plugins\Portfolio::getInstance();
		/** @var $previewNode \DOMElement */
		$previewNode = $this->root->appendChild( $this->xml->createElement( 'portfolio' ) );
		$Portfolio->getPortfolioPreviewXML( $previewNode );
		$previewNode->setAttribute( 'maxWidth', $Portfolio->settings['thumb_maxWidth']+35 );
	}

	public function loadAction() {
		
		$this->root->appendChild( $this->xml->createElement( 'portfolio-loadWork' ) );
		/** @var $contributorsXml \DOMElement */
		$contributorsXml = $this->root->appendChild( $this->xml->createElement( 'contributors' ) );
		Difra\Plugins\Portfolio::getInstance()->getContributorsXML( $contributorsXml );
	}

	public function contributorsAction() {

        $Users = \Difra\Plugins\Users::getInstance();
		$this->root->appendChild( $this->xml->createElement( 'portfolio-contributors' ) );
		
		$usersXml = $this->root->appendChild( $this->xml->createElement( 'users' ) );
		$Users->getListXML( $usersXml );
		/** @var $contributorsXml \DOMElement */
		$contributorsXml = $this->root->appendChild( $this->xml->createElement( 'contributors' ) );
		Difra\Plugins\Portfolio::getInstance()->getContributorsXML( $contributorsXml );
	}
	
	public function savecontributorAjaxAction( \Difra\Param\AjaxInt $user, \Difra\Param\AjaxString $name,
                                               \Difra\Param\AjaxString $role, \Difra\Param\AjaxString $linktext = null,
                                               \Difra\Param\AjaxInt $archive = null ) {

        $userSelect = intval( $user->val() );
        if( $userSelect == 0 ) {
            return $this->ajax->invalid( 'user' );
        }
        $linkText = is_null( $linktext ) ? null : $linktext->val();
        $archive = is_null( $archive ) ? null : intval( $archive->val() );

        $data = array( 'user' => $userSelect, 'name' => $name->val(), 'role' => $role->val(), 'linktext' => $linkText, 'archive' => $archive );
        \Difra\Plugins\Portfolio::getInstance()->saveContributor( $data );

        $this->ajax->redirect( '/adm/portfolio/contributors/' );
	}

	public function delcontributorAction( Difra\Param\NamedInt $id = null ) {
		Difra\Plugins\Portfolio::getInstance()->delContributor( $id->val() );
		$this->view->redirect( '/adm/portfolio/contributors/' );
	}

	public function editcontributorAction( Difra\Param\NamedInt $id = null ) {
		
		$this->root->appendChild( $this->xml->createElement( 'portfolio-editContributor' ) );
		/** @var $contNode \DOMElement */
		$contNode = $this->root->appendChild( $this->xml->createElement( 'contributor' ) );
		if( Difra\Plugins\Portfolio::getInstance()->getContributorXML( $id->val(), $contNode ) ) {
			
			// проверка на аватарку
			if( file_exists( DIR_HTDOCS . "/avatars/portfolio/" . $id->val() . "-large.png" ) ) {
				$contNode->setAttribute( 'avatar', true );
			}
			
		} else {
			$this->view->redirect( '/adm/portfolio/contributors/' );
		}
	}

	public function delavatarAction( Difra\Param\NamedInt $id = null ) {
		$id = $id->val();
		unlink( DIR_HTDOCS . "/avatars/portfolio/" . $id . "-large.png" );
		$this->view->redirect( '/adm/portfolio/editcontributor/id/' . $id . '/' );
	}

	public function saveworkAjaxAction( \Difra\Param\AjaxFile $workImage, \Difra\Param\AjaxString $name, \Difra\Param\AjaxString $date,
                                        \Difra\Param\AjaxString $workurl = null, \Difra\Param\AjaxString $linkText = null,
                                        \Difra\Param\AjaxString $software = null, \Difra\Param\AjaxHTML $description = null,
                                        \Difra\Param\AjaxFile $previewImage = null ) {

        if( !isset( $_POST['users'] ) || !isset( $_POST['userRole'] ) ) {
            return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'loadWork/plzAddUser' ) );
        }

        $Portfolio = \Difra\Plugins\Portfolio::getInstance();

        $workurl = !is_null( $workurl ) ? $workurl->val() : null;
        $linkText = !is_null( $linkText ) ? $linkText->val() : null;
        $software = !is_null( $software ) ? $software->val() : null;

        $dataArray = array( 'name' => $name->val(), 'date' => $date->val(), 'workurl' => $workurl,
                                'linkText' => $linkText, 'software' => $software, 'description' => $description,
                                'users' => $_POST['users'], 'userRole' => $_POST['userRole'] );

        $workId = $Portfolio->addWork( $dataArray );
        $Portfolio->saveImages( $workId, $workImage, $previewImage );

        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'loadWork/workAdded' ) );
        $this->ajax->redirect( '/adm/portfolio' );

	}

	public function deleteAjaxAction( Difra\Param\NamedInt $id = null ) {

		Difra\Plugins\Portfolio::getInstance()->delete( $id->val() );
		$this->ajax->refresh();
	}
	
	public function editAction( Difra\Param\NamedInt $id = null ) {

		/** @var $editNode \DOMElement */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'portfolio-edit' ) );
		$Portfolio = Difra\Plugins\Portfolio::getInstance();
		if( !$Portfolio->getWorkXML( $id->val(), $editNode ) ) {
			$this->view->redirect( '/adm/portfolio/' );
		}
		/** @var $contributorsXml \DOMElement */
		$contributorsXml = $this->root->appendChild( $this->xml->createElement( 'contributors' ) );
		$Portfolio->getContributorsXML( $contributorsXml );

	}
	
	public function deleteaddimageAction( Difra\Param\NamedString $id = null, Difra\Param\NamedInt $wid = null ) {
		
		Difra\Plugins\Portfolio::getInstance()->deleteAdditionalImage( $id->val(), $wid->val() );
		$this->view->redirect( '/adm/portfolio/edit/id/' . $wid->val() . '/' );
	}
	
	public function saveeditworkAjaxAction( Difra\Param\NamedInt $id, \Difra\Param\AjaxString $name, \Difra\Param\AjaxString $date,
                                            \Difra\Param\AjaxString $workurl = null, \Difra\Param\AjaxString $linkText = null,
                                            \Difra\Param\AjaxString $software = null, \Difra\Param\AjaxHTML $description = null,
                                            \Difra\Param\AjaxFile $workImage = null, \Difra\Param\AjaxFile $previewImage =null ) {

        if( ! isset( $_POST['users'] ) || ! isset( $_POST['userRole'] ) ) {
            return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'loadWork/plzAddUser' ) );
        }

        $Portfolio = \Difra\Plugins\Portfolio::getInstance();

        $workurl = ! is_null( $workurl ) ? $workurl->val() : null;
        $linkText = ! is_null( $linkText ) ? $linkText->val() : null;
        $software = ! is_null( $software ) ? $software->val() : null;

        $dataArray = array(
            'name' => $name->val(), 'date' => $date->val(), 'workurl' => $workurl, 'linkText' => $linkText,
            'software' => $software, 'description' => $description, 'users' => $_POST['users'], 'userRole' => $_POST['userRole'] );

        $Portfolio->update( $id->val(), $dataArray );

        // апдейт картинок
        if( !is_null( $workImage ) ) {
            $Portfolio->saveImages( $id->val(), $workImage, $previewImage );
        } elseif( !is_null( $previewImage ) ) {
            $Portfolio->savePreviewImage( $id->val(), $previewImage );
        }

        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'loadWork/wordEdited' ) );
        $this->ajax->redirect( '/adm/portfolio/' );

	}
	
	public function settingsAction() {
		
		$settingNode = $this->root->appendChild( $this->xml->createElement( 'portfolio-settings' ) );
		$settings = Difra\Plugins\Portfolio::getInstance()->settings;
		foreach( $settings as $key=>$value ) {
			$settingNode->appendChild( $this->xml->createElement( $key, $value ) );
		}
	}
	
	public function savesettingsAjaxAction( \Difra\Param\AjaxInt $maxWidth, \Difra\Param\AjaxInt $maxHeight,
                                            \Difra\Param\AjaxInt $thumb_maxWidth, \Difra\Param\AjaxInt $thumb_maxHeight ) {

        $settingsArray = array( 'maxWidth' => $maxWidth->val(), 'maxHeight' => $maxHeight->val(),
                                'thumb_maxWidth' => $thumb_maxWidth->val(), 'thumb_maxHeight' => $thumb_maxHeight->val() );

        \Difra\Plugins\Portfolio::getInstance()->saveSettings( $settingsArray );
        $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'settings/saved' ) );
	}
	
	public function showimagesAction( Difra\Param\NamedString $id = null ) {

		/** @var $editNode \DOMElement */
		$editNode = $this->root->appendChild( $this->xml->createElement( 'portfolio-showpics' ) );
		$Portfolio = Difra\Plugins\Portfolio::getInstance();
		if( !$Portfolio->getWorkXML( $id->val(), $editNode ) ) {
			$this->view->redirect( '/adm/portfolio/' );
		}
	}
	
}
