<?php

namespace Difra\Plugins\Users\Libs;
use Difra\Plugins\Users\userException;

class Check {

	private $user = null;

	/**
	 * @static
	 * @return Check
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Устанавливает объект пользователя
	 * @param \Difra\Plugins\Users\Objects\User $user
	 */
	public function setUserObject( \Difra\Plugins\Users\Objects\User $user ) {
		$this->user = $user;
	}

	/**
	 * Проверяет срок действия пароля
	 * @param $user
	 *
	 * @return bool
	 */
	public function expire() {

		$Settings = \Difra\Plugins\Users::getSettings();

		if( isset( $Settings['passwordExpire'] ) && $Settings['passwordExpire'] >0 ) {

			$diff = $Settings['passwordExpire'] * 86400;

			if( $this->user->passwordChanged == '0000-00-00 00:00:00' ) {

				$regDate = strtotime( $this->user->registered );
				if( $regDate+$diff <= time() ) {
					return false;
				}

			} else {
				$lastChange = strtotime( $this->user->passwordChanged );
				if( $lastChange+$diff <= time() ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Проверяет пароль
	 * @param $password
	 *
	 * @throws userException
	 */
	public function password( $password ) {
		if( $this->user->password != md5( $password ) ) {
			$_SESSION['userLoginAttempts']++;
			throw new userException( 'password', 'login_badpassword' );
		}
	}

	/**
	 * Проверяет логин пользователя
	 * @param $email
	 *
	 * @throws \Difra\Plugins\Users\userException
	 */
	public function login( $email ) {

		$Search = new \Difra\Unify\Search( 'UsersUser' );
		$Search->addConditions( array( 'email' => $email ) );
		$objects = $Search->doQuery();
		if( !is_null( $objects ) ) {
			throw new userException( 'email', 'duplicate' );
		}
	}

	/**
	 * Проверяет пароли устанавлевыемые пользователем
	 * @param $password
	 * @param $password2
	 *
	 * @throws \Difra\Plugins\Users\userException
	 */
	public function newPassword( $password, $password2 ) {

		if( is_null( $password ) ) {
			throw new userException( 'password', 'no_password' );
		}

		if( is_null( $password2 ) ) {
			throw new userException( 'password2', 'no_password' );
		}

		$Settings = \Difra\Plugins\Users::getSettings();

		// длина пароля
		if( isset( $Settings['length'] ) && $Settings['length'] !=0 ) {
			if( mb_strlen( $password ) < $Settings['length'] ) {
				throw new userException( 'password', 'too_short' );
			}
		}

		// сложность пароля
		if( isset( $Settings['strong'] ) && $Settings['strong'] == 1 ) {
			//TODO: добавить проверку сложности пароля
			// Проверять пароль на последовательность в разных раскладках!

			throw new userException( 'password', 'too_simple' );
		}

		// совпадение паролей
		if( $password != $password2 ) {
			throw new userException( 'password2', 'not_match' );
		}
	}

	/**
	 * Проверяет старый пароль
	 * @param $password
	 *
	 * @throws \Difra\Plugins\Users\userException
	 */
	public function oldPassword( $password ) {

		$Auth = \Difra\Auth::getInstance();

		if( !$id = $Auth->getId() ) {
			throw new userException( 'password', 'login_notfound' );
		}

		$Class = \Difra\Unify\Storage::getClass( 'UsersUser' );
		$User = $Class::get( $id );
		if( is_null( $User ) ) {
			throw new userException( 'password', 'login_notfound' );
		}

		if( $User->password != md5( $password ) ) {
			throw new userException( 'old', 'login_badpassword' );
		}
	}

	/**
	 * Проверяет флаги пользоватея
	 * @throws userException
	 */
	public function flags() {

		// проверка бана
		if( $this->user->banned == 1 ) {
			$_SESSION['userLoginAttempts']++;
			throw new userException( 'email', 'login_banned' );
		}
		// проверка активации пользователя
		if( $this->user->active == 0 ) {
			$_SESSION['userLoginAttempts']++;
			throw new userException( 'email', 'login_inactive' );
		}
	}

	/**
	 * Проверяет кол-во попыток авторизации и капчу
	 * @param $capcha
	 *
	 * @throws userException
	 */
	public function attempts( $capcha ) {

		$Settings = \Difra\Plugins\Users::getSettings();

		// проверка на кол-во попыток входа
		if( isset( $Settings['attempts'] ) && $Settings['attempts'] != 0 && is_null( $capcha ) ) {
			if( $_SESSION['userLoginAttempts'] >= $Settings['attempts'] ) {
				throw new userException( 'email', 'tooManyAttempts', true );
			}
		}
		// проверка капчи
		if( !is_null( $capcha ) ) {
			if( !\Difra\Libs\Capcha::getInstance()->verifyKey( $capcha ) ) {
				throw new userException( 'capcha', 'bad_capcha' );
			}
		}
	}

	public function recovery( $code ) {

		if( \Difra\Auth::getInstance()->isLogged() ) {
			throw new \Difra\Exception( 'logged' );
		}

		$Settings = \Difra\Plugins\Users::getSettings();
		$Class = \Difra\Unify\Storage::getClass( 'UsersRecovers' );

		$Recovery = $Class::get( $code );
		if( is_null( $Recovery ) ) {
			throw new \Difra\Exception( 'no_link' );
		}

		if( $Recovery->used > 0 ) {
			throw new \Difra\Exception( 'used' );
		}

		$UserClass = \Difra\Unify\Storage::getClass( 'UsersUser' );
		$User = $UserClass::get( $Recovery->userId );

		if( is_null( $User ) ) {
			throw new \Difra\Exception( 'no_user' );
		}

		if( $User->active == 0 ) {
			throw new \Difra\Exception( 'no_active' );
		}

		if( $User->banned == 1 ) {
			throw new \Difra\Exception( 'banned' );
		}

		$diffTime = 86400;
		$requestTime = strtotime( $Recovery->dateRequested );
		if( isset( $Settings['recoverTTL'] ) && $Settings['recoverTTL'] > 0 ) {

			if( $requestTime+$diffTime <= time() ) {
				throw new \Difra\Exception( 'rotten' );
			}
		}
	}

}
