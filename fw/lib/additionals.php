<?php

class Additionals {

	// проверка заполненности и валидности дополнительных полей из конфига сайта
	public static function checkAdditionals( $module, $data ) {

		if( !$conf = Site::getInstance()->getData( $module ) ) {
			return true;
		}
		if( !isset( $conf['fields'] ) or empty( $conf['fields'] ) ) {
			return true;
		}
		foreach( $conf['fields'] as $field => $flags ) {
			if( !$flags or empty( $flags ) ) {
				continue;
			}
			if( !is_array( $flags ) ) {
				$flags = array( $flags );
			}
			$db = MySQL::getInstance();
			foreach( $flags as $flag ) {
				switch( $flag ) {
					case 'required':
						if( empty( $data[$field] ) ) {
							return $field;
						}
						break;
					case 'unique':
						if( empty( $data[$field] ) ) {
							return $field;
						}
						$used = $db->fetchOne( "SELECT `id` FROM `{$module}_fields` WHERE `name`='" . $db->escape( $field ) . "' AND `value`='" . $db->escape( $data[$field] ) . "'" );
						if( $used ) {
							return $field;
						}
						break;
					case 'normal':
						break;
				}
			}
		}
		return true;
	}

	// сохранение дополнительных полей из конфига сайта
	public static function saveAdditionals( $module, $id, $data ) {

		if( !$conf = Site::getInstance()->getData( $module ) ) {
			return;
		}
		if( !isset( $conf['fields'] ) or empty( $conf['fields'] ) ) {
			return;
		}
		$db = MySQL::getInstance();
		foreach( $conf['fields'] as $field => $flags ) {
			if( !empty( $data[$field] ) ) {
				$db->query( "REPLACE INTO `{$module}_fields` (`id`,`name`,`value`) VALUES ('" . $db->escape( $id ) . "','" . $db->escape( $field ) . "','" . $db->escape( $data[$field] ) . "')" );
			}
		}
	}

	/**
	 * Additionals::getAdditionals()
	 * @desc Возвращает массив с дополнительными полями
	 * @param string $module
	 * @param integer $id
	 * @return array || boolean
	 */
	public static function getAdditionals( $module, $id ) {

		$db = MySQL::getInstance();
		$query = "SELECT `name`, `value` FROM `{$module}_fields` WHERE `id`='" . $db->escape( $id ) . "'";
		$res = $db->fetch( $query );
		$fields = array();
		foreach( $res as $k => $data ) {
			$fields[$data['name']] = $data['value'];
		}
		return!empty( $fields ) ? $fields : false;
	}

	/**
	 * Additionals::getAdditionalsXml()
	 * @desc Добавляет xml с дополнительными полями
	 * @param string $module
	 * @param integer $id
	 * @param DOMNode $node
	 * @return void
	 */
	public static function getAdditionalsXml( $module, $id, $node ) {

		if( $data = self::getAdditionals( $module, $id ) ) {
			$addNode = $node->appendChild( $node->ownerDocument->createElement( $module . 'Additionals' ) );
			if( !empty( $data ) ) {
				foreach( $data as $k => $v ) {
					$addNode->setAttribute( $k, $v );
				}
			}
		}
	}

	public static function getAdditionalId( $module, $name, $value ) {

		$db = MySQL::getInstance();
		$id = $db->fetchOne( "SELECT `id` FROM `{$module}_fields` WHERE `name`='" . $db->escape( $name ) . "' AND `value`='" . $db->escape( $value ) . "'" );
		return $id ? $id : null;
	}

	public static function getAdditionalValue( $module, $id, $name ) {
		
		$db = MySQL::getInstance();
		$id = $db->fetchOne( "SELECT `value` FROM `{$module}_fields` WHERE `id`='" . $db->escape( $id ) . "' AND `name`='" . $db->escape( $name ) . "'" );
		return $id ? $id : null;
	}
	
	/**
	 * Additionals::unSetAdditionalField()
	 * @desc Удаляет дополнительное поле
	 * @param string $module
	 * @param integer $id
	 * @param string $name
	 * @return void
	 */
	public static function unSetAdditionalField( $module, $id, $name ) {
		if( !$conf = Site::getInstance()->getData( $module ) ) {
			return;
		}
		if( isset( $conf['fields'][$name] ) ) {
			$db = MySQL::getInstance();
			$db->query( "DELETE FROM `{$module}_fields` WHERE `id`='" . $db->escape( $id ) . "' AND `name`='" . $db->escape( $name ) . "'" );
		}
	}
}

