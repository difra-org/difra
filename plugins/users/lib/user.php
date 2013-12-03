<?php

namespace Difra\Plugins\Users;
use Difra\Unify\Storage, Difra\Plugins\Users\Libs\Check, Difra\Plugins\Users\Libs\Notify;

class User {

	const IP_MASK = '255.255.0.0'; // маска проверки ip

	private $id = null;
	private $email = null;

	private $activation = null;

	private $active = 0;
	private $banned = 0;
	private $moderator = 0;

	private $registered = null;
	private $logged = null;

	private $additionals = null;

	private $authStatus = null;

	/**
	 * Записывает данные пользователя по его id
	 * @param       $id
	 * @param array $fields
	 * @param bool $authUpdate
	 */
	public static function save( $id, array $fields, $authUpdate = false ) {

		$Auth = \Difra\Auth::getInstance();

		if( !empty( $fields ) ) {
			$User = \Difra\Unify\Storage::getClass( 'UsersUser' );
			$object = $User::get( (string)$id );

			foreach( $fields as $key => $value ) {
				$object->$key = $value;
				if( $authUpdate && $key != 'password' ) {
					$Auth->data[$key] = $value;
				}
			}
			if( $authUpdate ) {
				$Auth->update();
			}
		}
	}

	/**
	 * Создаёт пользователя
	 * @param       $email
	 * @param       $password
	 * @param       $password2
	 * @param array $flags
	 *
	 * @return int|null
	 */
	public static function create( $email, $password, $password2, array $flags = null ) {

		$Check = Check::getInstance();
		$Settings = \Difra\Plugins\Users::getSettings();

		// проверям логин
		$Check->login( $email );

		// проверяем пароли
		$Check->newPassword( $password, $password2 );

		$Class = Storage::getClass( 'UsersUser' );
		$User = $Class::create();
		$User->email = strtolower( $email );
		$User->password = md5( $password );

		if( isset( $Settings['activeType'] ) ) {

			switch( $Settings['activeType'] ) {

				case 'no':
					$User->active = 1;
				break;

				case 'manual':
				case 'email':
					if( isset( $flags['active'] ) && $flags['active'] == 1 ) {
						$User->active = 1;
					} else {
						$User->active = 0;
						$User->activation = \Difra\Libs\Capcha::getInstance()->genKey( 24 );
					}
				break;
			}
		}

		if( isset( $flags['banned'] ) && $flags['banned'] == 1 ) {
			$User->banned = 1;
		}
		if( isset( $flags['moderator'] ) && $flags['moderator'] == 1 ) {
			$User->moderator = 1;
		}

		$User->save();
		$UserId = $User->getPrimaryValue();

		if( !is_null( $UserId ) ) {

			if( $Settings['activeType'] == 'email' || $Settings['activeType'] == 'manual' && $User->active == 0 ) {

				Notify::sendActivation( $User->email, $User->activation, $password, $Settings['activeType'] );
			}

		} else {
			throw new \Difra\Exception( 'Can\'t create user' );
		}

		return $User->getPrimaryValue();
	}

	/**
	 * Получаем пользователя по его ID
	 * @param $id
	 * @param $withAdditionals
	 * @return User|null
	 */
	public static function getById( $id, $withAdditionals = true ) {

		$User = new self;
		$class = Storage::getClass( 'UsersUser' );
		$object = $class::get( $id );

		try {
			$object->load();
		} catch( \Difra\Exception $ex ) {
			return null;
		}

		$User->id = intval( $id );
		$User->email = $object->email;
		$User->activation = $object->activation;
		$User->active = $object->active;
		$User->banned = $object->banned;
		$User->moderator = $object->moderator;
		$User->registered = $object->registered;
		$User->logged = $object->logged;

		if( $withAdditionals ) {
			$User->additionals = Additionals::getAdditionalsById( $id );
		}

		return $User;
	}

	/**
	 * Возвращает xml пользователя
	 * @param \DOMNode $node
	 */
	public function getXML( \DOMNode $node ) {

		$Locale = \Difra\Locales::getInstance();

		$node->setAttribute( 'id', $this->id );
		$node->setAttribute( 'email', $this->email );
		$node->setAttribute( 'active', $this->active );
		$node->setAttribute( 'banned', $this->banned );
		$node->setAttribute( 'moderator', $this->moderator );
		$node->setAttribute( 'registered', $Locale->getDateFromMysql( $this->registered, true ) );
		if( $this->logged != '0000-00-00 00:00:00' ) {
			$loggedDate = $Locale->getDateFromMysql( $this->logged, true );
		} else {
			$loggedDate = null;
		}
		$node->setAttribute( 'logged', $loggedDate );

		if( !is_null( $this->additionals ) ) {
			$addNode = $node->appendChild( $node->ownerDocument->createElement( 'additionals' ) );
			foreach( $this->additionals as $key => $value ) {
				$itemNode = $addNode->appendChild( $node->ownerDocument->createElement( 'field' ) );
				$itemNode->setAttribute( 'name', $key );
				$itemNode->setAttribute( 'value', $value );

				// локализованные названия дополнительных полей
				$localeName = $Locale->getXPath( 'users/fields/' . $key );

				if( $localeName !='' && $localeName !='No language item for: users/fields/' . $key ) {
					$itemNode->setAttribute( 'localeName', $localeName );
				}
			}
		}
	}

	/**
	 * Авторизация пользователя
	 * @param      $email
	 * @param      $password
	 * @param boolean $rememberMe
	 * @param string|null $capcha
	 */
	public static function login( $email, $password, $rememberMe, $capcha = null ) {

		\Difra\Envi\Session::start();
		$Settings = \Difra\Plugins\Users::getSettings();
		$Check = Check::getInstance();

		if( !isset( $_SESSION['userLoginAttempts'] ) ) {
			throw new userException( 'email', 'noCookies' );
		}

		$Search = new \Difra\Unify\Search( 'UsersUser' );
		$Search->addConditions( array( 'email' => $email ) );
		$objects = $Search->doQuery();

		// проверка попыток входа и капчи
		$Check->attempts( $capcha );

		// проверка логина
		if( is_null( $objects ) ) {
			$_SESSION['userLoginAttempts']++;
			throw new userException( 'email', 'login_notfound' );
		}

		$user = $objects[0];
		$Check->setUserObject( $user );

		// проверка пароля
		$Check->password( $password );

		// проверка флагов
		$Check->flags();

		$user->logged = date( 'Y-m-d H:i:s', time() );

		$userAdditionals = Additionals::getAdditionalsById( $user->id );

		$userData = array( 'id' => $user->id, 'email' => $user->email, 'active' => $user->active,
				   'banned' => $user->banned, 'moderator'=> $user->moderator, 'activation' => $user->activation,
				   'registered' => $user->registered, 'logged' => $user->logged );

		// успешная авторизация, убиваем флаги попыток и капчи
		unset( $_SESSION['userLoginAttempts'] );
		unset( $_SESSION['needCapcha'] );

		// проверяем просроченность пароля
		if( !$Check->expire( $user ) ) {
			$userAdditionals['passwordExpired'] = true;
			$userAdditionals['passwordExpireDays'] = $Settings['passwordExpire'];
		}

		\Difra\Auth::getInstance()->login( $user->email, $userData, $userAdditionals );

		$Cookies   = \Difra\Libs\Cookies::getInstance();
		$Cookies->set( 'logged', $user->logged );

		if( $rememberMe ) {
			self::_setLongSession( $user->id );
		}
	}

	/**
	 * Активация пользователя
	 * @param $code
	 */
	public static function activation( $code ) {

		$Search = new \Difra\Unify\Search( 'UsersUser' );
		$Search->addConditions( array( 'active' => 0, 'banned' => 0, 'activation' => $code ) );
		$users = $Search->doQuery();

		if( is_null( $users ) ) {
			throw new userException( 'email', 'login_notfound' );
		}
		$user = $users[0];

		// активируем пользователя
		$user->active = 1;
		$user->activation = null;
		$user->logged = date( 'Y-m-d H:i:s', time() );

		// авторизируем пользователя
		$userData = array( 'id' => $user->id, 'email' => $user->email, 'active' => $user->active,
				   'banned' => $user->banned, 'moderator'=> $user->moderator, 'activation' => $user->activation,
				   'registered' => $user->registered, 'logged' => $user->logged );

		$userAdditionals = Additionals::getAdditionalsById( $user->id );
		\Difra\Auth::getInstance()->login( $user->email, $userData, $userAdditionals );

		$Cookies   = \Difra\Libs\Cookies::getInstance();
		$Cookies->set( 'logged', $user->logged );
	}

	/**
	 * Устанавливает пользовательскую сессию авторизации
	 * @param $userId
	 */
	private static function _setLongSession( $userId ) {

		$sessionId = sha1( uniqid() ) . substr( sha1( uniqid() ), 1, 8 );
		$Cookies   = \Difra\Libs\Cookies::getInstance();
		$Cookies->setExpire( time() + 31 * 3 * 24 * 60 * 60 );
		$Cookies->set( 'resume', $sessionId );

		$Class = Storage::getClass( 'UsersSessions' );
		$object = $Class::create();
		$object->id = $userId;
		$object->session_id = $sessionId;
		$object->ip = ip2long( $_SERVER['REMOTE_ADDR'] );
		$object->save( true );
	}

	/**
	 * Проверяет пользовательскую сессию авторизации и при необходимости логинит пользователя
	 */
	public static function checkLongSession() {

		if( \Difra\Auth::getInstance()->isLogged() ) {
			return;
		}

		if( !isset( $_COOKIE['resume'] ) || strlen( $_COOKIE['resume'] ) != 48 ) {
			return;
		}

		//TODO: Доделать !!!

	}

}
