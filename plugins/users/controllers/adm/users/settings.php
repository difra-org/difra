<?php

use Difra\Plugins, Difra\Param;

class AdmUsersSettingsController extends Difra\Controller {

	public function dispatch() {
		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'users-settings' ) );
		Plugins\Users::getSettingsXML( $mainXml );

	}

	public function saveAjaxAction( Param\AjaxInt $length, Param\AjaxInt $attempts,
					Param\AjaxCheckbox $strong, Param\AjaxInt $passwordExpire,
					Param\AjaxString $activeType, Param\AjaxInt $recoverTTL,
					Param\AjaxCheckbox $sendNotify, Param\AjaxString $notifyMails = null ) {

		$settingsArray = array( 'length' => $length->val(), 'attempts' => $attempts->val(),
					'strong' => $strong->val(), 'passwordExpire' => $passwordExpire->val(),
					'activeType' => $activeType->val(), 'recoverTTL' => $recoverTTL->val(),
					'sendNotify' => $sendNotify->val(),
					'notifyMails' => !is_null( $notifyMails ) ? $notifyMails->val() : null );

		Plugins\Users::saveSettings( $settingsArray );

		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'users/adm/settings/saved' ) );
		$this->ajax->refresh();

	}

}
