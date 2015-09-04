<?php

namespace Difra\Plugins;
use Difra;

class Users {

	const MIN_PASSWORD_LENGTH = 6;
	const RECOVER_TTL         = 72; // hours
	const ACTIVATE_TTL = 7; // days
	const IP_MASK = '255.255.0.0'; // маска проверки ip

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {
	}

	const REGISTER_EMAIL_EMPTY     = 'email_empty';
	const REGISTER_BAD_EMAIL       = 'bad_email';
	const REGISTER_EMAIL_BUSY      = 'email_busy';
	const REGISTER_PASSWORD1_EMPTY = 'password1_empty';
	const REGISTER_PASSWORD2_EMPTY = 'password2_empty';
	const REGISTER_PASSWORDS_DIFF  = 'password_diff';
	const REGISTER_PASSWORD_SHORT  = 'password_short';
	const REGISTER_FAILED          = 'register_failed';

	// проверка заполненности основных полей в форме регистрации
	private function _checkRegisterFields( $data ) {

		if( empty( $data['email'] ) ) {
			return self::REGISTER_EMAIL_EMPTY;
		}
		if( empty( $data['password1'] ) ) {
			return self::REGISTER_PASSWORD1_EMPTY;
		}
		if( empty( $data['password2'] ) ) {
			return self::REGISTER_PASSWORD2_EMPTY;
		}
		if( !$this->isEmailValid( $data['email'] ) ) {
			return self::REGISTER_BAD_EMAIL;
		}
		if( strlen( $data['password1'] ) < self::MIN_PASSWORD_LENGTH ) {
			return self::REGISTER_PASSWORD_SHORT;
		}
		if( $data['password1'] != $data['password2'] ) {
			return self::REGISTER_PASSWORDS_DIFF;
		}
		if( $this->checkLogin( $data['email'] ) ) {
			return self::REGISTER_EMAIL_BUSY;
		}
		return true;
	}

	// регистрация пользователя
	public function register( $data ) {

		$data2 = array();
		foreach( $data as $k => $v ) {
			$data2[$k] = trim( $v );
		}

		if( true !== ( $res = $this->_checkRegisterFields( $data ) ) ) {
			return $res;
		}
		$data['email'] = strtolower( $data['email'] );

		if( true !== ( $res = Difra\Additionals::checkAdditionals( 'users', $data ) ) ) {
			return $res;
		}

		$mysql = Difra\MySQL::getInstance();
		$query = "INSERT INTO `users` SET `email`='" . $mysql->escape( $data['email'] ) . "', `password`='" . md5( $data['password1'] ) . "'";

		switch( $confirm = $this->getActivationMethod() ) {
			/** @noinspection PhpMissingBreakStatementInspection */
		case 'email':
			do {
				$key = strtolower( Difra\Libs\Capcha::getInstance()->genKey( 24 ) );
				$d   = $mysql->fetch( "SELECT `id` FROM `users` WHERE `activation`='$key'" );
			} while( !empty( $d ) );
			$data['activation'] = $key;
			$query .= ", `activation`='$key', `active`=0";
			break;
		case 'moderate':
			$query .= ', `active`=0';
			break;
		case 'none':
		default:
		}

		if( false === $mysql->query( $query ) ) {
			return self::REGISTER_FAILED;
		}
		$userId = $mysql->getLastId();
		Difra\Additionals::saveAdditionals( 'users', $userId, $data );

		$this->_registrationMail( $data, $confirm );
		return true;
	}

	public function getActivationMethod() {

		$conf = \Difra\Config::getInstance()->get( 'users' );
		return isset( $conf['confirm'] ) ? $conf['confirm'] : 'none';
	}

	private function _registrationMail( $data, $confirm = 'none' ) {

		$data2 = array(
			'user'           => $data['email'],
			'confirm'        => $confirm,
			'ttl'            => self::ACTIVATE_TTL,
			'password'       => $data['password1'],
			'code'           => $data['activation']
		);
		Difra\Mailer::getInstance()->CreateMail( $data['email'], 'mail_registration', $data2 );
	}

	const ACTIVATE_NOTFOUND = 'activate_notfound';
	const ACTIVATE_USED     = 'activate_used';

	public function activate( $key ) {

		$key  = trim( $key );
		$db   = Difra\MySQL::getInstance();
		$data = $db->fetch( "SELECT * FROM `users` WHERE `activation`='" . $db->escape( $key ) . "'" );
		if( empty( $data ) ) {
			return static::ACTIVATE_NOTFOUND;
		}
		$data = $data[0];
		if( $data['active'] ) {
			return static::ACTIVATE_USED;
		}
		$db->query( "UPDATE `users` SET `active`='1' WHERE `activation`='" . $db->escape( $key ) . "'" );
		return true;
	}

	// проверка валидности e-mail'ов
	public function isEmailValid( $email ) {

		return preg_match( '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$/', $email ) ? true : false;
	}

	const LOGIN_NOTFOUND    = 'login_notfound';
	const LOGIN_BADPASSWORD = 'login_badpassword';
	const LOGIN_INACTIVE    = 'login_inactive';
	const LOGIN_BANNED      = 'login_banned';

	public function login( $email, $password, $remember, $withAdditionals = false ) {

		$mysql = Difra\MySQL::getInstance();
		$email = strtolower( $email );
		$additionals = null;
		$data  = $mysql->fetch( 'SELECT * FROM `users` WHERE `email`=\'' . $mysql->escape( $email ) . "'" );
		if( empty( $data ) ) {
			return self::LOGIN_NOTFOUND;
		}
		$data = $data[0];
		if( $data['password'] != md5( $password ) ) {
			return self::LOGIN_BADPASSWORD;
		}
		if( !$data['active'] ) {
			return self::LOGIN_INACTIVE;
		}
		if( $data['banned'] ) {
			return self::LOGIN_BANNED;
		}

		if( $withAdditionals == true ) {

			$additionalsData = $mysql->fetch( "SELECT `name`, `value` FROM `users_fields` WHERE `id`='" . intval( $data['id'] ) . "'" );
			if( !empty( $additionalsData ) ) {
				foreach( $additionalsData as $k=>$tempData ) {
					$additionals[$tempData['name']] = $tempData['value'];
				}
			}
		}

		Difra\Auth::getInstance()->login( $email, $data, $additionals );
		if( $remember ) {
			$this->_setLongSession( $data['id'] );
		}
		$mysql->query( 'UPDATE `users` SET `logged`=NOW() WHERE `email`=\'' . $mysql->escape( $email ) . "'" );
		return true;
	}

	public function recover( $email ) {

		$mysql = Difra\MySQL::getInstance();
		$data  = $mysql->fetch( 'SELECT * FROM `users` WHERE `email`=\'' . $mysql->escape( $email ) . "'" );
		if( empty( $data ) ) {
			return self::LOGIN_NOTFOUND;
		}
		$data = $data[0];
		if( !$data['active'] ) {
			return self::LOGIN_INACTIVE;
		}
		if( $data['banned'] ) {
			return self::LOGIN_BANNED;
		}
		do {
			$key = strtolower( Difra\Libs\Capcha::getInstance()->genKey( 24 ) );
			$d   = $mysql->fetch( 'SELECT `id` FROM `users_recovers` WHERE `id`=\'' . $key . "'" );
		} while( !empty( $d ) );
		$mysql->query( "INSERT INTO `users_recovers` (`id`,`user_id`) VALUES ('$key','{$data['id']}')" );
		Difra\Mailer::getInstance()->CreateMail( $data['email'], 'mail_recover', array( 'code' => $key, 'ttl' => self::RECOVER_TTL ) );
		return true;
	}

	const RECOVER_INVALID  = 'recover_invalid';
	const RECOVER_USED     = 'recover_used';
	const RECOVER_OUTDATED = 'recover_outdated';

	public function verifyRecover( $key ) {

		$db   = Difra\MySQL::getInstance();
		$data = $db->fetch( "SELECT * FROM `users_recovers` WHERE `id`='" . $db->escape( $key ) . "'" );
		if( empty( $data ) ) {
			return self::RECOVER_INVALID;
		}
		$data = $data[0];
		if( $data['used'] ) {
			return self::RECOVER_USED;
		}
		$date = $data['date_requested'];
		$date = explode( ' ', $date );
		$day  = explode( '-', $date[0] );
		$time = explode( ':', $date[1] );
		$day1 = mktime( $time[0], $time[1], $time[2], $day[1], $day[2], $day[0] );
		if( $day1 and ( time() - $day1 > 1440 * 60 * 3 ) ) {
			return self::RECOVER_OUTDATED;
		}
		return true;
	}

	public function recoverSetPassword( $key, $pw1, $pw2 ) {

		$db   = Difra\MySQL::getInstance();
		$data = $db->fetch( "SELECT * FROM `users_recovers` WHERE `id`='" . $db->escape( $key ) . "'" );
		if( empty( $data ) ) {
			return self::RECOVER_INVALID;
		}
		$data = $data[0];
		if( ( $r = $this->setPassword( $data['user_id'], $pw1, $pw2 ) ) !== true ) {
			return $r;
		}
		$db->query( 'UPDATE `users_recovers` SET `used`=1,`date_used`=\'' . date( 'Y-m-d H:i:s' ) . "' WHERE `id`='" . $db->escape( $key ) . "'" );
		return true;
	}

	const PW_EMPTY = 'pw_empty';
	const PW_SHORT = 'pw_short';
	const PW_DIFF  = 'pw_diff';

	/**
	 * Устанавливает новый пароль пользователю
	 * @param int    $user
	 * @param string $pw1
	 * @param string $pw2
	 *
	 * @return boolean
	 */
	public function setPassword( $user, $pw1, $pw2 ) {

		$pw1 = trim( $pw1 );
		$pw2 = trim( $pw2 );
		if( empty( $pw1 ) ) {
			return self::PW_EMPTY;
		}
		if( strlen( $pw1 ) < self::MIN_PASSWORD_LENGTH ) {
			return self::PW_SHORT;
		}
		if( $pw1 != $pw2 ) {
			return self::PW_DIFF;
		}
		$auth = Difra\Auth::getInstance();
		if( $auth->logged and $auth->getId() == $user ) {
			$auth->data['password'] = md5( $pw1 );
			$auth->update();
		}
		$db = Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `password`='" . md5( $pw1 ) . "' WHERE `id`='" . intval( $user ) . "'" );
		return true;
	}

	/**
	 * Смена пароля текущего пользователя
	 * Возвращает строку с кодом ошибки или true в случае успеха
	 *
	 * @param string $oldPass
	 * @param string $newPass
	 *
	 * @return mixed
	 */
	const PW_BADOLD = 'pw_badold';

	public function changePassword( $oldPass, $newPass ) {

		$auth = Difra\Auth::getInstance();
		if( !$auth->logged ) {
			throw new Difra\Exception( "Can't changePassword() for unauthorized user" );
		}
		if( !$this->verifyPassword( $oldPass ) ) {
			return static::PW_BADOLD;
		}
		return $this->setPassword( $auth->getId(), $newPass, $newPass );
	}

	public function verifyPassword( $password ) {

		return md5( $password ) == \Difra\Auth::getInstance()->data['password'];
	}

	public function setInfo( $data ) {

		$auth = Difra\Auth::getInstance();
		if( !$auth->logged ) {
			return false;
		}
		$db   = Difra\MySQL::getInstance();
		$data = $db->escape( serialize( $data ) );
		$db->query( "UPDATE `users` SET `info`='$data' WHERE `id`='" . $db->escape( $auth->data['id'] ) . "'" );
		$auth->data['info'] = $data;
		$auth->update();
		return true;
	}

	public function getInfo() {

		$auth = Difra\Auth::getInstance();
		if( !$auth->logged ) {
			return false;
		}
		return @unserialize( $auth->data['info'] );
	}

	/**
	 * @param \DOMElement|\DOMNode $node
	 */
	public function getInfoXML( $node ) {

		/** @var \DOMElement|\DOMNode $infoNode */
		$infoNode = $node->appendChild( $node->ownerDocument->createElement( 'userInfo' ) );
		$data     = $this->getInfo();
		if( !empty( $data ) ) {
			foreach( $data as $k => $v ) {
				$infoNode->setAttribute( $k, $v );
			}
		}
	}

	/**
	 * Возвращает xml со списком всех пользователей
	 *
	 * @param \DOMElement|\DOMNode $node
	 * @param int                  $page
	 * @param int                  $perPage
	 */
	public function getListXML( $node, $page = 1, $perPage = 75 ) {

		$db       = \Difra\MySQL::getInstance();
		$rawTotal = $db->fetchRow( "SELECT COUNT(`id`) AS `count` FROM `users`" );
		$total    = intval( $rawTotal['count'] );
		$first    = ( $page - 1 ) * $perPage;

		$node->setAttribute( 'total', $total );
		$node->setAttribute( 'first', $first );
		$node->setAttribute( 'last', $first + $perPage );
		$node->setAttribute( 'pages', floor( ( ( $total - 1 ) / $perPage ) + 1 ) );

		$db->fetchXML( $node,
			       "SELECT `id`,`email`,`active`,`banned`,`registered`,`logged`,`info`, `moderator` FROM `users` LIMIT {$first}, {$perPage}" );
	}

	public function getUserXML( \DOMNode $node, $id ) {

		$mysql = Difra\MySQL::getInstance();
		$mysql->fetchXML( $node, 'SELECT `id`,`email`,`active`,`banned`,`registered`,`logged`,`info`,`moderator` FROM `users` WHERE `id`=' .
					 $mysql->escape( $id ) );

		// вывод дополнительных полей
		$query = "SELECT * FROM `users_fields` WHERE `id`='" . intval( $id ) . "'";
		$res = $mysql->fetch( $query );
		if( !empty( $res ) ) {
			$addonNode = $node->appendChild( $node->ownerDocument->createElement( 'addon_fields' ) );

			foreach( $res as $k=>$data ) {
				$fieldItem = $addonNode->appendChild( $node->ownerDocument->createElement( 'field' ) );
				$fieldItem->setAttribute( 'name', $data['name'] );
				$fieldItem->setAttribute( 'value', $data['value'] );
			}

		}

	}

	public function setUserLogin( $id, $data ) {

		$mysql = Difra\MySQL::getInstance();
		if( empty( $data['email'] ) or !trim( $data['email'] ) ) {
			return false;
		}
		$email  = $mysql->escape( trim( $data['email'] ) );
		$passwd = false;
		if( !empty( $data['change_pw'] ) and $data['change_pw'] ) {
			if( !empty( $data['new_pw'] ) and trim( $data['new_pw'] ) ) {
				$passwd = trim( $data['new_pw'] );
			}
		}
		$mysql->query(
			"UPDATE `users` SET `email`='$email'" . ( $passwd ? ",`password`='" . md5( $passwd ) . "'" : '' ) . ' WHERE `id`='
			. $mysql->escape( $id )
		);

		if( isset( $data['addonFields'] ) && isset( $data['addonValues'] )
			&& !is_null( $data['addonFields'] ) && !is_null( $data['addonValues'] ) ) {

			// сохранение дополнительных полей пользоватея

			$query[] = "DELETE FROM `users_fields` WHERE `id`='" . intval( $id ) . "'";
			$values = array();
			foreach( $data['addonFields'] as $k=>$fieldName ) {

				if( isset( $data['addonValues'][$k] ) && $data['addonValues'][$k] !='' ) {
					$values[] = "( '" . intval( $id ) . "', '" . $mysql->escape( $fieldName ) .
						"', '" . $mysql->escape( $data['addonValues'][$k] ) . "' )";
				}

			}
			if( !empty( $values ) ) {
				$query[] = "INSERT INTO `users_fields` (`id`, `name`, `value`) VALUES " . implode( ', ', $values );
				$mysql->query( $query );
			}
		}

		return true;
	}

	public function checkLogin( $email ) {

		$mysql = Difra\MySQL::getInstance();
		$check = $mysql->fetch( 'SELECT `id` FROM `users` WHERE `email`=\'' . $mysql->escape( $email ) . "'" );
		return !empty( $check ) ? true : false;
	}

	// проверяет поля unique на дубликаты в базе
	public function checkUniqueFields( $field, $data ) {

		$conf = \Difra\Config::getInstance()->get( 'users' );
		if( !$conf ) {
			return false;
		}
		if( !isset( $conf['fields'] ) or empty( $conf['fields'] ) ) {
			return false;
		}
		if( !isset( $conf['fields'][$field] ) || $conf['fields'][$field] != 'unique' ) {
			return false;
		}
		// проверяем поле
		$mysql = Difra\MySQL::getInstance();
		$res   = $mysql->fetch( 'SELECT `id` FROM `users_fields` WHERE `name`=\'' . $mysql->escape( $field ) .
					'\' AND `value`=\'' . $mysql->escape( $data ) . '\'' );
		return !empty( $res ) ? true : false;
	}

	public function ban( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `banned`=1 WHERE `id` = '" . intval( $id ) . "'" );
	}

	public function unBan( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `banned`=0 WHERE `id` = '" . intval( $id ) . "'" );
	}

	public function setModerator( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `moderator`=1 WHERE `id` = '" . intval( $id ) . "'" );
	}

	public function unSetModerator( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `moderator`=0 WHERE `id` = '" . intval( $id ) . "'" );
	}

	private function _setLongSession( $id ) {

		$db        = \Difra\MySQL::getInstance();
		$sessionId = sha1( uniqid() ) . substr( sha1( uniqid() ), 1, 8 );
		$Cookies   = \Difra\Libs\Cookies::getInstance();
		$Cookies->setExpire( time() + 31 * 3 * 24 * 60 * 60 );
		$Cookies->set( 'resume', $sessionId );

		$db->query( "REPLACE INTO `users_sessions` SET `id`='" . intval( $id ) .
			    "', `session_id`='" . $sessionId . "', `ip`='" . ip2long( $_SERVER['REMOTE_ADDR'] ) . "'" );
	}

	public function unSetLongSession( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `users_sessions` WHERE `id`='" . intval( $id ) . "'" );

		\Difra\Libs\Cookies::getInstance()->remove( 'resume' );
	}

	public static function unSetLongSessionBySID( $sessionId ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `users_sessions` WHERE `session_id`='" . $db->escape( $sessionId ) . "'" );
		\Difra\Libs\Cookies::getInstance()->remove( 'resume' );
	}

	public static function checkLongSession() {

		if( \Difra\Auth::getInstance()->isLogged() ) {
			return;
		}

		if( isset( $_COOKIE['resume'] ) and strlen( $_COOKIE['resume'] ) == 48 ) {

			// проверяем наличие длинной сессии
			$db   = \Difra\MySQL::getInstance();
			$data = $db->fetchRow( "SELECT s.`ip`, u.*
					FROM `users_sessions` s
					RIGHT JOIN `users` AS `u` ON u.`id` = s.`id` AND u.`active`=1 AND u.`banned`=0
					WHERE s.`session_id`='" . $db->escape( $_COOKIE['resume'] ) . "'" );
			if( empty( $data ) ) {
				self::unSetLongSessionBySID( $_COOKIE['resume'] );
				return;
			}

			// проверяем IP и логиним юзера
			$currentIp = $_SERVER['REMOTE_ADDR'];
			preg_match( '/\\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/', $currentIp, $t );
			if( !empty( $t ) ) {
				$currentNetwork = $t[0] . '.0.0';
				if( ( $data['ip'] & ip2long( self::IP_MASK ) ) == ip2long( $currentNetwork ) ) {
					// можно залогинить юзера
					$email = strtolower( $data['email'] );

					$additionals = null;

					$additionalsData = $db->fetch( "SELECT `name`, `value` FROM `users_fields` WHERE `id`='" .
										intval( $data['id'] ) . "'" );
					if( !empty( $additionalsData ) ) {
						foreach( $additionalsData as $k=>$tempData ) {
							$additionals[$tempData['name']] = $tempData['value'];
						}
					}

					Difra\Auth::getInstance()->login( $email, $data, $additionals );
					return;
				}
			}
			self::unSetLongSessionBySID( $_COOKIE['resume'] );
		}
	}

	/**
	 * Возвращает id юзера по его активации
	 *
	 * @param $code
	 *
	 * @return bool
	 */
	public function getUserIdByActivation( $code ) {

		$db = \Difra\MySQL::getInstance();
		$r  = $db->fetchRow( "SELECT `id` FROM `users` WHERE `activation`='" . $db->escape( $code ) . "' AND `active`=1" );
		return isset( $r['id'] ) ? $r['id'] : false;
	}

	/**
	 * Активирует пользоватля. Для ручной активации из админки
	 * @param $id
	 */
	public function manualActivation( $id ) {

		$db = Difra\MySQL::getInstance();
		$db->query( "UPDATE `users` SET `active`=1 WHERE `id`='" . intval( $id ) . "'" );
	}

}

