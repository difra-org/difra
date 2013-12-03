<?php

namespace Difra\Plugins;

class Users {

	/**
	 * Выводит список пользователей с учетом фильтра
	 * @param \DOMNode $node
	 * @param int      $page
	 */
	public static function getListXML( \DOMNode $node, $page = 1 ) {

		$Users = new \Difra\Unify\Search( 'UsersUser' );

		$Users->setPage( intval( $page ) );
		$Users->setPerpage( 40 );

		$Filter = new \Difra\Plugins\Users\Filter();
		$Filter->setConditions( $Users );

		$Users->getListXML( $node );
	}

	/**
	 * Возвращает настройки работы с пользователями
	 * @return array|null
	 */
	public static function getSettings() {

		$settings = \Difra\Config::getInstance()->get( 'users_settings' );
		return $settings;
	}

	/**
	 * Устанавливает настройки работы с пользователями в xml
	 * @param \DOMNode $node
	 */
	public static function getSettingsXML( \DOMNode $node ) {

		$settings = self::getSettings();
		if( !empty( $settings ) ) {
			foreach( $settings as $key=>$value ) {
				$node->setAttribute( $key, $value );
			}
		}
	}

	/**
	 * Сохраняет настройки работы с пользователями
	 * @param array $settingsArray
	 */
	public static function saveSettings( array $settingsArray ) {

		\Difra\Config::getInstance()->set( 'users_settings', $settingsArray );
	}

}