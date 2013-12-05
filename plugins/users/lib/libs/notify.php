<?php

namespace Difra\Plugins\Users\Libs;
use Difra\Mailer;

class Notify {

	/**
	 * Отправляет письмо с ссылкой на восстановление пароля
	 * @param $email
	 * @param $code
	 */
	public static function sendRecovery( $email, $code ) {

		$Settings = \Difra\Plugins\Users::getSettings();

		$ttl = 24;

		if( isset( $Settings['recoverTTL'] ) && $Settings['recoverTTL'] != '' ) {
			$ttl = $Settings['recoverTTL'];
		}

		$mailData = array( 'link' => \Difra\Envi::getHost() . '/auth/recover/password/' . $code,
				   'email' => $email,
				   'siteName' => \Difra\Envi::getHost(), 'ttl' => $ttl );

		Mailer::getInstance()->createMail( $email, 'mail_recover', $mailData );
	}

	/**
	 * Отправляет письмо активации и уведомление о новом пользователе
	 * @param $email
	 * @param $code
	 * @param $password
	 * @param $confirmType
	 */
	public static function sendActivation( $email, $code, $password, $confirmType ) {

		$Settings = \Difra\Plugins\Users::getSettings();
		$Mailer = Mailer::getInstance();

		$mailData = array( 'link' => \Difra\Envi::getHost() . '/auth/activation/' . $code,
				   'email' => $email,
				   'password' => $password,
				   'host' => \Difra\Envi::getHost(),
				   'confirm' => $confirmType );

		$Mailer->createMail( $email, 'mail_activation', $mailData );

		if( isset( $Settings['sendNotify'] ) && $Settings['sendNotify'] == 1 ) {

			if( isset( $Settings['notifyMails'] ) && $Settings['notifyMails'] !='' ) {

				$mails = explode( ',', $Settings['notifyMails'] );
				if( !empty( $mails ) ) {

					foreach( $mails as $mail ) {
						$Mailer->createMail( $mail, 'mail_newuser', $mailData );
					}
				}
			}
		}
	}

	/**
	 * Отправляет оповещение о ручной активации пользователя
	 * @param $userId
	 */
	public static function sendManualActivated( $userId ) {

		$Settings = \Difra\Plugins\Users::getSettings();

		if( !isset( $Settings['sendActiveNotify'] ) || $Settings['sendActiveNotify'] == 0 ) {
			return;
		}

		$Class = \Difra\Unify\Storage::getClass( 'UsersUser' );
		$User = $Class::get( $userId );
		if( !is_null( $User ) ) {

			$mailData = array( 'host' => \Difra\Envi::getHost() );

			Mailer::getInstance()->createMail( $User->email, 'mail_activated', $mailData );
		}
	}

}