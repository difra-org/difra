<?php

use Difra\Plugins\Users, Difra\Param;

class AuthController extends Difra\Controller {

	/**
	 * Форма логина
	 * @return void
	 */
	public function authorizationAjaxAction() {

		if( \Difra\Auth::getInstance()->logged ) {
			$this->ajax->reload();
			return;
		}
		$this->root->appendChild( $this->xml->createElement( 'login' ) );
		$this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
	}

	/**
	 * Логин в систему
	 * @param Difra\Param\AjaxString   $email
	 * @param Difra\Param\AjaxString   $password
	 * @param Difra\Param\AjaxCheckbox $rememberMe
	 *
	 * @return void
	 */
	public function loginAjaxAction( Param\AjaxString $email, Param\AjaxString $password, Param\AjaxCheckbox $rememberMe ) {

		$auth = Difra\Auth::getInstance();
		if( $auth->logged ) {
			$this->ajax->reload();
			return;
		}
		$users = Users::getInstance();
		$res   = $users->login( $email->val(), $password->val(), ( $rememberMe->val() == 1 ) ? true : false );
		if( $res === true ) {
			$this->ajax->reload();
			return;
		}
		switch( $res ) {
		case Users::LOGIN_BADPASSWORD:
			$this->ajax->invalid( 'password', \Difra\Locales::getInstance()->getXPath( 'auth/login/' . $res ) );
			break;
		default:
			$this->ajax->invalid( 'email', \Difra\Locales::getInstance()->getXPath( 'auth/login/' . $res ) );
		}
	}

	/**
	 * Выход из системы
	 * @return void
	 */
	public function logoutAjaxAction() {

		$Auth = Difra\Auth::getInstance();
		$id   = $Auth->getId();
		$Auth->logout();

		// в случае ручного логаута убираем длинную сессию
		\Difra\Plugins\Users::getInstance()->unSetLongSession( $id );

		// TODO: сделать так, чтобы в случаях, если страница требует авторизации, происходил редирект на главную
		$this->ajax->reload();
	}

	/**
	 * Форма восстановления пароля
	 * @return void
	 */
	public function recoveryAjaxAction() {

		if( \Difra\Auth::getInstance()->logged ) {
			$this->ajax->reload();
			return;
		}
		$this->root->appendChild( $this->xml->createElement( 'recover' ) );
		$this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
	}

	/**
	 * Восстановление пароля
	 * @param \Difra\Param\AjaxString $email
	 *
	 * @return void
	 */
	public function recoverAjaxAction( Param\AjaxString $email ) {

		$auth = Difra\Auth::getInstance();
		if( $auth->logged ) {
			$this->ajax->reload();
			return;
		}
		$users = Users::getInstance();
		$res   = $users->recover( $email->val() );
		$this->ajax->setResponse( 'error', $res );
		if( $res !== true ) {
			$this->ajax->invalid( 'email', \Difra\Locales::getInstance()->getXPath( 'auth/login/' . $res ) );
			return;
		}
		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'auth/login/recovered' ) );
		$this->ajax->close();
	}

	/**
	 * Ссылки из писем для восстановления пароля
	 * @param Difra\Param\AnyString $code
	 *
	 * @return void
	 */
	public function recoverAction( Param\AnyString $code ) {

		$code = trim( $code->val() );
		if( ctype_alnum( $code ) ) {
			\Difra\Libs\Cookies::getInstance()->query( '/auth/recover2/' . $code );
		} else {
			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'auth/recover/bad_link' ) );
		}
		$this->view->redirect( '/' );
	}

	/**
	 * Форма установки нового пароля
	 * @param Difra\Param\AnyString $code
	 *
	 * @return void
	 */
	public function recover2AjaxAction( Param\AnyString $code ) {

		if( Difra\Auth::getInstance()->logged ) {
			$this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'auth/recover/already_logged' ) );
			return;
		}
		$res = Users::getInstance()->verifyRecover( $code->val() );
		if( $res !== true ) {
			$this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'auth/recover/' . $res ) );
			return;
		}
		$recoverNode = $this->root->appendChild( $this->xml->createElement( 'recover2' ) );
		$recoverNode->setAttribute( 'code', $code->val() );
		$this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
	}

	/**
	 * Сохранение нового пароля
	 * @param Difra\Param\AnyString  $code
	 * @param Difra\Param\AjaxString $password1
	 * @param Difra\Param\AjaxString $password2
	 *
	 * @return void
	 */
	public function recover3AjaxAction( Param\AnyString $code, Param\AjaxString $password1, Param\AjaxString $password2 ) {

		$locales = \Difra\Locales::getInstance();
		if( Difra\Auth::getInstance()->logged ) {
			$this->ajax->error( $locales->getXPath( 'auth/recover/already_logged' ) );
			return;
		}
		$res = Users::getInstance()->verifyRecover( $code->val() );
		if( $res !== true ) {
			$this->ajax->error( $locales->getXPath( 'auth/recover/' . $res ) );
			return;
		}
		$error = \Difra\Plugins\Users::getInstance()->recoverSetPassword( $code->val(), $password1->val(), $password2->val() );
		if( $error !== true ) {
			echo $error;
			$this->ajax->invalid( 'password1', $locales->getXPath( 'auth/recover/' . $error ) );
			return;
		}
		$this->ajax->notify( $locales->getXPath( 'auth/recover/done' ) );
		$this->ajax->close();
	}

	/**
	 * Форма регистрации
	 * @return void
	 */
	public function registerAjaxAction() {

		if( \Difra\Auth::getInstance()->logged ) {
			$this->ajax->reload();
			return;
		}
		$this->root->appendChild( $this->xml->createElement( 'register' ) );
		$this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
	}

	/**
	 * Проверка формы регистрации и сама регистрация
	 * @param Difra\Param\AjaxCheckbox    $accept
	 * @param Difra\Param\AjaxString|null $email
	 * @param Difra\Param\AjaxString|null $password1
	 * @param Difra\Param\AjaxString|null $password2
	 * @param Difra\Param\AjaxString|null $capcha
	 *
	 * @return void
	 */
	public function register2AjaxAction( Param\AjaxCheckbox $accept, Param\AjaxString $email = null, Param\AjaxString $password1 = null,
					     Param\AjaxString $password2 = null, Param\AjaxString $capcha = null ) {

		$auth    = Difra\Auth::getInstance();
		$locales = \Difra\Locales::getInstance();
		if( $auth->logged ) {
			$this->ajax->error( $locales->getXPath( 'auth/register/already_logged' ) );
			return;
		}
		$users = Users::getInstance();
		$ok    = true;

		if( !$email or !$email->val() ) {
			$this->ajax->status( 'email', $locales->getXPath( 'auth/register/email_empty' ), 'error' );
			$ok = false;
		} elseif( !$users->isEmailValid( $email->val() ) ) {
			$this->ajax->status( 'email', $locales->getXPath( 'auth/register/email_invalid' ), 'error' );
			$ok = false;
		} elseif( $users->checkLogin( $email->val() ) ) {
			$this->ajax->status( 'email', $locales->getXPath( 'auth/register/email_dupe' ), 'error' );
			$ok = false;
		} else {
			$this->ajax->status( 'email', $locales->getXPath( 'auth/register/email_ok' ), 'ok' );
		}
		if( !$password1 or !$password1->val() ) {
			$this->ajax->status( 'password1', $locales->getXPath( 'auth/register/password1_empty' ), 'error' );
			$ok = false;
		} elseif( strlen( $password1->val() ) < 6 ) {
			$this->ajax->status( 'password1', $locales->getXPath( 'auth/register/password1_short' ), 'error' );
			$ok = false;
		} else {
			$this->ajax->status( 'password1', $locales->getXPath( 'auth/register/password1_ok' ), 'ok' );
		}
		if( !$password2 or !$password2->val() ) {
			$this->ajax->status( 'password2', $locales->getXPath( 'auth/register/password2_empty' ), 'error' );
			$ok = false;
		} elseif( $password1->val() != $password2->val() ) {
			$this->ajax->status( 'password2', $locales->getXPath( 'auth/register/passwords_diff' ), 'error' );
			$ok = false;
		} else {
			$this->ajax->status( 'password2', $locales->getXPath( 'auth/register/password2_ok' ), 'ok' );
		}
		if( !$capcha or !$capcha->val() ) {
			$this->ajax->status( 'capcha', $locales->getXPath( 'auth/register/capcha_empty' ), 'error' );
			$ok = false;
		} elseif( !\Difra\Libs\Capcha::getInstance()->verifyKey( $capcha->val() ) ) {
			$this->ajax->status( 'capcha', $locales->getXPath( 'auth/register/capcha_invalid' ), 'error' );
		} else {
			$this->ajax->status( 'capcha', $locales->getXPath( 'auth/register/capcha_ok' ), 'ok' );
		}

		$addit = \Difra\Additionals::getStatus( 'users', $this->ajax->parameters );
		if( is_array( $addit ) and !empty( $addit ) ) {
			foreach( $addit as $name => $status ) {
				switch( $status ) {
				case \Difra\Additionals::FIELD_OK:
					$this->ajax->status( $name, $locales->getXPath( 'additionals/users/' . $name . '/ok' ), 'ok' );
					break;
				case \Difra\Additionals::FIELD_EMPTY:
					$this->ajax->status( $name,
							     $locales->getXPath( 'additionals/users/' . $name . '/empty' ), 'error' );
					$ok = false;
					break;
				case \Difra\Additionals::FIELD_DUPE:
					$this->ajax->status( $name,
							     $locales->getXPath( 'additionals/users/' . $name . '/dupe' ), 'error' );
					$ok = false;
					break;
				case \Difra\Additionals::FIELD_BAD:
					$this->ajax->status( $name, $locales->getXPath( 'additionals/users/' . $name . '/bad_symbols' ), 'error' );

					$ok = false;
					break;
				}
			}
		}
		if( !$ok ) {
			return;
		}

		// TODO: опцию для включения показа EULA
		/*
		if( !$accept->val() ) {
			$this->root->appendChild( $this->xml->createElement( 'eula' ) );
			$this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
			return;
		}
		*/

		$users = Users::getInstance();
		$res   = $users->register( $this->ajax->parameters );
		if( $res === true ) {
			$this->ajax->notify( $locales->getXPath( 'auth/register/complete-' . Users::getInstance()->getActivationMethod() ) );
			$this->ajax->close();
		} else {
			$this->ajax->error( 'Unknown error: ' . $res );
		}
	}

	/**
	 * Активация учётных записей (по ссылке из e-mail)
	 *
	 * @param \Difra\Param\AnyString $code
	 *
	 * @return void
	 */
	public function activateAction( Param\AnyString $code ) {

		$res = Users::getInstance()->activate( $code );
		if( $res === true ) {
			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'auth/activate/done' ) );
		} else {
			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'auth/activate/' . $res, true ) );
		}
		$this->view->redirect( '/' );
	}

	/**
	 * Смена пароля с указанием старого
	 * @param Difra\Param\AjaxString $oldpassword
	 * @param Difra\Param\AjaxString $password1
	 * @param Difra\Param\AjaxString $password2
	 *
	 * @return void
	 */
	public function changepasswordAjaxActionAuth( Param\AjaxString $oldpassword, Param\AjaxString $password1, Param\AjaxString $password2 ) {

		$ok      = true;
		$locales = \Difra\Locales::getInstance();
		if( !Users::getInstance()->verifyPassword( $oldpassword ) ) {
			$this->ajax->invalid( 'oldpassword', $locales->getXPath( 'auth/password/bad_old' ) );
			$ok = false;
		}
		if( !$password1 or !$password1->val() ) {
			$this->ajax->invalid( 'password1', $locales->getXPath( 'auth/register/password1_empty' ) );
			$ok = false;
		} elseif( strlen( $password1->val() ) < 6 ) {
			$this->ajax->invalid( 'password1', $locales->getXPath( 'auth/register/password1_short' ) );
			$ok = false;
		}
		if( !$password2 or !$password2->val() ) {
			$this->ajax->invalid( 'password2', $locales->getXPath( 'auth/register/password2_empty' ) );
			$ok = false;
		} elseif( $password1->val() != $password2->val() ) {
			$this->ajax->invalid( 'password2', $locales->getXPath( 'auth/register/passwords_diff' ) );
			$ok = false;
		}
		if( $ok ) {
			$this->ajax->notify( $locales->getXPath( 'auth/password/changed' ) );
			$this->ajax->reset();
		}
	}
}

