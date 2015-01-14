<?php

use Difra\Plugins, Difra\Param;

class AuthRecoverController extends Difra\Controller {

	/**
	 * Форма востановления пароля
	 */
	public function indexAction() {

		$this->root->appendChild( $this->xml->createElement( 'auth-recover' ) );
	}

	/**
	 * Окно востановления пароля
	 */
	public function indexAjaxAction() {

		$windowXml = new DOMDocument();
		$root = $windowXml->appendChild( $windowXml->createElement( 'root' ) );
		\Difra\Locales::getInstance()->getLocaleXML( $root );
		$mainXml = $root->appendChild( $windowXml->createElement( 'auth-recover' ) );

		$this->ajax->display( \Difra\View::render( $windowXml, 'forms', true ) );
	}

	/**
	 * Отправляет письмо с ссылкой востановления пароля
	 * @param Param\AjaxEmail $email
	 */
	public function sendAjaxAction( Param\AjaxEmail $email ) {

		try{
			Plugins\Users\Recovers::sendRecover( $email->val() );

		} catch( Plugins\Users\userException $ex ) {
			return;
		} catch( \Difra\Exception $ex ) {
			return;
		}

		$this->ajax->load( '#recoverForm', '<span>' . \Difra\Locales::getInstance()->getXPath( 'recover/recovered' ) . '</span>' );
	}

	/**
	 * Форма востановления пароля
	 * @param Param\AnyString $code
	 */
	public function passwordAction( Param\AnyString $code ) {

		$windowXml = new DOMDocument();
		$rootXml = $windowXml->appendChild( $windowXml->createElement( 'root' ) );
		\Difra\Locales::getInstance()->getLocaleXML( $rootXml );
		$mainXml = $rootXml->appendChild( $windowXml->createElement( 'user-recovery-change' ) );
		$mainXml->setAttribute( 'code', $code->val() );

		try{
			Plugins\Users\Libs\Check::getInstance()->recovery( $code->val() );
		} catch( \Difra\Exception $ex ) {
			$mainXml->setAttribute( 'error', $ex->getMessage() );
		}

		\Difra\Libs\Cookies::getInstance()->notify( \Difra\View::render( $windowXml, 'windows', true ) );
		\Difra\View::redirect( '/' );
	}

	public function changeAjaxAction( Param\AnyString $code,
					  Param\AjaxString $password, Param\AjaxString $password2 ) {

		try{
			Plugins\Users\Libs\Check::getInstance()->newPassword( $password->val(), $password2->val() );

		} catch( Plugins\Users\userException $ex ) {
			return;
		}

		try{
			$userId = Plugins\Users\Recovers::getIdByRecovery( $code->val() );

		} catch( Plugins\Users\userException $ex ) {
			return;
		}

		Plugins\Users\User::save( $userId, array( 'password' => md5( $password->val() ),
							  'passwordChanged' => date( 'Y-m-d H:i:s', time() ) ) );

		Plugins\Users\Recovers::useRecover( $code->val() );

		$this->ajax->load( '.message', '<span>' .\Difra\Locales::getInstance()->getXPath( 'recover/used' ) . '</span>' );
	}

}