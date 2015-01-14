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

	/**
	 * Возвращает полный список всех пользователей с их параметрами и выбранными дополнительными полями
	 * @param \DOMNode $node
	 * @param array    $fields
	 * @param array    $joinedFields
	 */
	public static function getCustomListXML( \DOMNode $node, array $fields = null, array $joinedFields = null ) {

		$db = \Difra\MySQL::getInstance();
		$mainFields = array();
		$mainFieldsString = '';
		$joinSelectFields = array();
		$joinSelectFieldsString = '';
		$joinString = '';

		if( !empty( $fields ) ) {
			foreach( $fields as $key ) {
				$mainFields[] = "u.`" . $key . "`";
			}
			$mainFieldsString = ', ' . implode( ', ', $mainFields );
		}

		if( !empty( $joinedFields ) ) {
			foreach( $joinedFields as $key ) {
				$joinSelectFields[] = "`uf_" . $key . "`.`value` AS `" . $key . "`";

				$joinString .= " LEFT JOIN `users_fields` AS `uf_" . $key .
							"` ON u.`id` = `uf_" . $key . "`.`id` AND `uf_" . $key . "`.`name`='" . $key . "' ";
			}
			$joinSelectFieldsString = ', ' . implode( ', ', $joinSelectFields );
		}

		$query = "SELECT u.`id`, u.`email`" . $mainFieldsString . $joinSelectFieldsString . " FROM `users` u " . $joinString;
		$res = $db->fetch( $query );

		if( !empty( $res ) ) {
			$usersNode = $node->appendChild( $node->ownerDocument->createElement( 'users' ) );
			foreach( $res as $k=>$data ) {
				$userNode = $usersNode->appendChild( $node->ownerDocument->createElement( 'user' ) );
				foreach( $data as $key=>$value ) {
					$userNode->setAttribute( $key, $value );
				}
			}
		}
	}

}