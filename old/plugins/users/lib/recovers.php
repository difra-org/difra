<?php

namespace Difra\Plugins\Users;
use Difra\Unify\Storage, Difra\Plugins\Users\Libs\Notify;

class Recovers {

	/**
	 * Создаёт запись восстановления пароля
	 * @param $email
	 *
	 * @throws userException
	 */
	public static function sendRecover( $email ) {

		$UserClass = Storage::getClass( 'UsersUser' );
		$User = $UserClass::getByField( 'email', $email );

		if( is_null( $User ) ) {
			throw new userException( 'email', 'login_notfound' );
		}

		// создаём рекавери запись
		$Recovery = Storage::getClass( 'UsersRecovers' );
		$RecoveryObject = $Recovery::create();
		$RecoveryObject->id = \Difra\Libs\Capcha::getInstance()->genKey( 24 );
		$RecoveryObject->userId = $User->id;

		Notify::sendRecovery( $User->email, $RecoveryObject->id );
	}

	/**
	 * Возвращает ID пользователя по его рековери-строке
	 * @param $code
	 *
	 * @return mixed
	 * @throws userException
	 */
	public static function getIdByRecovery( $code ) {

		$Class = Storage::getClass( 'UsersRecovers' );
		$Recover = $Class::get( $code );
		if( is_null( $Recover ) ) {
			throw new userException( 'password', 'no_recover' );
		}

		return $Recover->userId;
	}

	/**
	 * Делает ссылку восстановления пароля использованной
	 * @param $code
	 */
	public static function useRecover( $code ) {

		$Class = Storage::getClass( 'UsersRecovers' );
		$Recover = $Class::get( $code );
		$Recover->used = 1;
		$Recover->dateUsed = date( 'Y-m-d H:i:s', time() );
	}

}

