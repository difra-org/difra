<?php

namespace Difra\Plugins\Users;

class userException extends \Exception {

	/**
	 * Создаёт ошибку заполнения одного из полей
	 * @param string $field
	 * @param string $status
	 */
	public function __construct( $field, $status, $needCapcha = false ) {

		$Ajax = \Difra\Ajaxer::getInstance();

		if( $needCapcha == true ) {
			$_SESSION['needCapcha'] = true;
			$Ajax->refresh();
			return;
		}

		$statusText = \Difra\Locales::getInstance()->getXPath( 'users/errors/' . $status );
		if( $statusText == '' || $statusText == 'No language item for: users/errors/' . $status ) {
			$statusText = $status;
		}

		$Ajax->invalid( $field );
		$Ajax->status( $field, $statusText, 'problem' );
	}

}