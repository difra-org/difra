<?php

use Difra\Plugins\VideoManager;

class AdmVideomanagerController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$videoXml = $this->root->appendChild( $this->xml->createElement( 'video-manager' ) );
		$VM = \Difra\Plugins\videoManager::getInstance();

		$inNode = $videoXml->appendChild( $this->xml->createElement( 'videoIn' ) );
		$VM->getInVideosXML( $inNode );


		$outNode = $videoXml->appendChild( $this->xml->createElement( 'videoOut' ) );
		$VM->getAddedVideosXML( $outNode );
	}

	public function deleteAjaxAction( \Difra\Param\NamedString $name ) {

		$Locale = \Difra\Locales::getInstance();

		if( \Difra\Plugins\videoManager::getInstance()->deleteFile( $name->val() ) ) {

			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/deleted' ) .
				'<br/><br/><a class="button" href="#" onclick="window.location.reload();">' .
				$Locale->getXPath( 'videoManager/adm/close' ) . '</a>' );

		} else {
			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/errors/noDeleter' )
				. '<br/><br/><a class="button" href="#" onclick="ajaxer.close( this );">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );
		}
	}

	public function addvideoAjaxAction( \Difra\Param\AjaxString $filename, \Difra\Param\AjaxString $name, \Difra\Param\AjaxFile $poster = null ) {

		$poster = !is_null( $poster ) ? $poster->val() : null;
		$Locale = \Difra\Locales::getInstance();
		$res = \Difra\Plugins\videoManager::getInstance()->addVideo( $filename->val(), $name->val(), $poster );

		if( $res === true ) {
			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/added' )
				. '<br/><br/><a class="button" href="#" onclick="window.location.reload();">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );
		} else {
			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/errors/' . $res )
				. '<br/><br/><a class="button" href="#" onclick="ajaxer.close( this );">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );
		}
	}

	public function encodeAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\videoManager::getInstance()->changeStatus( $id->val(), 1 );

		$Locale = \Difra\Locales::getInstance();
		$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/addEncode' )
			. '<br/><br/><a class="button" href="#" onclick="window.location.reload();">' . $Locale->getXPath( 'videoManager/adm/close' )
			. '</a>' );
	}

	public function deleteaddedAjaxAction( \Difra\Param\AnyInt $id ) {

		$Locale = \Difra\Locales::getInstance();
		if( \Difra\Plugins\videoManager::getInstance()->deleteAddedVideo( $id->val() ) ) {

			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/videoDeleted' )
				. '<br/><br/><a class="button" href="#" onclick="window.location.reload();">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );

		} else {

			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/errors/noDelete' )
				. '<br/><br/><a class="button" href="#" onclick="window.location.reload();">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );
		}
	}

	public function changeposterAjaxAction( \Difra\Param\AjaxString $videoHash, \Difra\Param\AjaxFile $poster ) {

		$Locale = \Difra\Locales::getInstance();
		$res = \Difra\Plugins\videoManager::getInstance()->savePoster( $videoHash->val(), $poster->val() );
		if( $res===true ) {

			$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/posterAdded' )
				. '<br/><br/><a class="button" href="#" onclick="window.location.reload();">' . $Locale->getXPath( 'videoManager/adm/close' )
				. '</a>' );
		} else {
			$this->ajax->display(
				$Locale->getXPath( 'videoManager/adm/errors/' . $res ) . '<br/><br/><a class="button" href="#" onclick="ajaxer.close( this );">'
					. $Locale->getXPath( 'videoManager/adm/close' ) . '</a>' );
		}

	}

	public function changenameAjaxAction( \Difra\Param\AjaxInt $videoId, \Difra\Param\AjaxString $name ) {

		$Locale = \Difra\Locales::getInstance();
		\Difra\Plugins\videoManager::getInstance()->changeName( $videoId->val(), $name->val() );
		$this->ajax->display( $Locale->getXPath( 'videoManager/adm/notify/nameChanged' ) .
					'<br/><br/><a class="button" href="#" onclick="window.location.reload();">' .
					$Locale->getXPath( 'videoManager/adm/close' ) . '</a>' );
	}

}