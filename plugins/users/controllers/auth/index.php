<?php

use Difra\Plugins, Difra\Param, Difra\Auth;

class AuthIndexController extends Difra\Controller {

	public function dispatch() {
		\Difra\Envi\Session::start();
	}

	/**
	 * Форма авторизации
	 */
	public function indexAction() {

		if( !isset( $_SESSION['userLoginAttempts'] ) ) {
			$_SESSION['userLoginAttempts'] = 1;
		}

		if( Auth::getInstance()->isLogged() ) {
			\Difra\View::redirect( '/' );
		}

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'auth-form' ) );
		if( isset( $_SESSION['needCapcha'] ) && $_SESSION['needCapcha'] == true ) {
			$mainXml->setAttribute( 'showCapcha', true );
		}
	}

	/**
	 * Форма авторизации для выдачи аяксом
	 */
	public function indexAjaxAction() {

		if( Auth::getInstance()->isLogged() ) {
			return $this->ajax->reload();
		}

		if( !isset( $_SESSION['userLoginAttempts'] ) ) {
			$_SESSION['userLoginAttempts'] = 1;
		}

		$authXml = new DOMDocument();
		$rootNode = $authXml->appendChild( $authXml->createElement( 'root' ) );
		$mainNode = $rootNode->appendChild( $authXml->createElement( 'auth-form' ) );


		\Difra\Locales::getInstance()->getLocaleXML( $rootNode );

		if( isset( $_SESSION['needCapcha'] ) && $_SESSION['needCapcha'] == true ) {
			$mainNode->setAttribute( 'showCapcha', true );
		}

		$this->ajax->display( \Difra\View::render( $authXml, 'forms', true ) );
	}

	/**
	 * Логаут для аякса
	 */
	public function logoutAjaxAction() {

		$Auth = Difra\Auth::getInstance();
		$id   = $Auth->getId();
		$Auth->logout();

		// в случае ручного логаута убираем длинную сессию
		//\Difra\Plugins\Users::getInstance()->unSetLongSession( $id );

		// TODO: сделать так, чтобы в случаях, если страница требует авторизации, происходил редирект на главную
		$this->ajax->reload();
	}

	/**
	 * Логаут
	 */
	public function logoutAction() {

		$Auth = Difra\Auth::getInstance();
		$id   = $Auth->getId();
		$Auth->logout();

		// в случае ручного логаута убираем длинную сессию
		//\Difra\Plugins\Users::getInstance()->unSetLongSession( $id );

		// TODO: сделать так, чтобы в случаях, если страница требует авторизации, происходил редирект на главную
		\Difra\View::redirect( '/' );

	}

	/**
	 * Авторизация
	 * @param Param\AjaxEmail    $email
	 * @param Param\AjaxString   $password
	 * @param Param\AjaxCheckbox $rememberMe
	 * @param Param\AjaxString   $capcha
	 */
	public function loginAjaxAction( Param\AjaxEmail $email, Param\AjaxString $password,
					 Param\AjaxCheckbox $rememberMe, Param\AjaxString $capcha = null ) {

		if( Auth::getInstance()->isLogged() ) {
			return $this->ajax->reload();
		}

		$capcha = !is_null( $capcha ) ? $capcha->val() : null;

		try{
			Plugins\Users\User::login( $email->val(), $password->val(), ($rememberMe->val() == 1) ? true : false, $capcha );
		} catch( Plugins\Users\userException $ex ) {
			return;
		} catch( \Difra\Exception $ex ) {
			return;
		}

		$this->ajax->close();

		$Settings = Plugins\Users::getSettings();
		if( isset( $Settings['behavior'] ) && $Settings['behavior'] == 'redirect' ) {

			if( isset( $Settings['redirectURI'] ) && $Settings['redirectURI'] !='' ) {

				if( substr( $Settings['redirectURI'], 0, 1 ) == '/' ) {
					$this->ajax->redirect( $Settings['redirectURI'] );
				} else {
					$this->ajax->redirect( '/' . $Settings['redirectURI'] );
				}

			} else {
				$this->ajax->redirect( '/' );
			}

		} else {
			$this->ajax->reload();
		}
	}


	/**
	 * Активация авторизированного пользователя
	 * @param Param\AnyString $code
	 */

	public function activationActionAuth( Param\AnyString $code ) {

		$windowXml = new DOMDocument();
		$windowRoot = $windowXml->appendChild( $windowXml->createElement( 'root' ) );
		\Difra\Locales::getInstance()->getLocaleXML( $windowRoot );

		$mainXml = $windowRoot->appendChild( $windowXml->createElement( 'user-activated' ) );

		$mainXml->setAttribute( 'authActivation', true );
		\Difra\Libs\Cookies::getInstance()->notify( \Difra\View::render( $windowXml, 'windows', true ) );
		\Difra\View::redirect( '/' );

	}

	/**
	 * Активация пользователя
	 * @param Param\AnyString $code
	 */
	public function activationAction( Param\AnyString $code ) {

		$widnowXml = new DOMDocument();

		$windowRoot = $widnowXml->appendChild( $widnowXml->createElement( 'root' ) );
		\Difra\Locales::getInstance()->getLocaleXML( $windowRoot );
		$mainXml = $windowRoot->appendChild( $widnowXml->createElement( 'user-activated' ) );

		try {
			Plugins\Users\User::activation( $code );
		} catch( Plugins\Users\userException $ex ) {

			$mainXml->setAttribute( 'badActivation', true );

		} catch( \Difra\Exception $ex ) {

		}

		\Difra\Libs\Cookies::getInstance()->notify( \Difra\View::render( $widnowXml, 'windows', true ) );
		\Difra\View::redirect( '/' );
	}

	/**
	 * Форма смены пароля
	 */
	public function changepasswordActionAuth() {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'user-password' ) );

	}

	public function changepasswordAjaxActionAuth() {

		$windowXml = new DOMDocument();
		$rootXml = $windowXml->appendChild( $windowXml->createElement( 'root' ) );
		\Difra\Locales::getInstance()->getLocaleXML( $rootXml );
		$rootXml->appendChild( $windowXml->createElement( 'user-password' ) );

		$this->ajax->display( \Difra\View::render( $windowXml, 'forms', true ) );
	}

	public function passwordAjaxActionAuth( Param\AjaxString $old, Param\AjaxString $password, Param\AjaxString $password2 ) {

		$Check = Plugins\Users\Libs\Check::getInstance();

		try{
			$Check->oldPassword( $old->val() );
		} catch( Plugins\Users\userException $ex ) {
			return;
		}

		try{
			$Check->newPassword( $password->val(), $password2->val() );
		} catch( Plugins\Users\userException $ex ) {
			return;
		}

		$fields = array( 'password' => md5( $password->val() ), 'passwordChanged' => date( 'Y-m-d H:i:s', time() ) );

		Plugins\Users\User::save( Auth::getInstance()->getId(), $fields, true );
		$this->ajax->load( '#passwordChange', '<span>' . \Difra\Locales::getInstance()->getXPath( 'changePassword/changed' ) . '</span>' );
	}

}