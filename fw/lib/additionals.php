<?php

namespace Difra;

class Additionals {

	// проверка заполненности и валидности дополнительных полей из конфига сайта
	public static function checkAdditionals( $module, $data ) {

		$err = self::getStatus( $module, $data );
		if( is_array( $err ) and !empty( $err ) ) {
			foreach( $err as $k => $v ) {
				if( $v != static::FIELD_OK ) {
					return $k;
				}
			}
		}
		return true;
	}

	const FIELD_OK = 'field_ok';
	const FIELD_EMPTY = 'field_empty';
	const FIELD_DUPE = 'field_dupe';
	const FIELD_BAD = 'field_bad';
	public static function getStatus( $module, $data ) {

		$err = array();
		$conf = Site::getInstance()->getData( $module );
		if( !$conf or !isset( $conf['fields'] ) or empty( $conf['fields'] ) ) {
			return $err;
		}
		foreach( $conf['fields'] as $field => $flags ) {
			$res = static::FIELD_OK;
			if( !$flags or empty( $flags ) ) {
				$err[$field] = $res;
				continue;
			}
			if( !is_array( $flags ) ) {
				$flags = array( $flags );
			}
			$value = !empty( $data[$field] ) ? trim( $data[$field] ) : '';
			$db = MySQL::getInstance();
			foreach( $flags as $flag ) {
				switch( $flag ) {
				case 'required':
					if( $value === '' ) {
						$res = static::FIELD_EMPTY;
					}
					break;
				case 'unique':
					if( $value === '' ) {
						$res = static::FIELD_EMPTY;
						break;
					}
					$used =
						$db->fetchOne( "SELECT `id` FROM `{$module}_fields` WHERE `name`='" . $db->escape( $field )
							       . "' AND `value`='" . $db->escape( trim( $data[$field] ) ) . "'" );
					if( $used ) {
						$res = static::FIELD_DUPE;
					}
					if( !preg_match( '/^[a-z0-9-_а-я]+$/iu', trim( $data[$field] ) ) ) {
						$res = static::FIELD_BAD;
					}
					break;
				case 'normal':
					break;
				}
			}
			$err[$field] = $res;
		}
		return $err;
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

	// сохраняет все дополнительные поля, без ограничений конфига
	public static function saveAllAdditionals( $module, $id, $data ) {

		$db = MySQL::getInstance();
		$query = array();
		$db->query( "DELETE FROM `{$module}_fields` WHERE `id`='" . intval( $id ) . "'" );
		foreach( $data as $key=>$value ) {
			$query[] = "REPLACE INTO `{$module}_fields` (`id`, `name`, `value`) VALUES ( '" . intval( $id ) . "',
			 		'" . $db->escape( $key ) . "', '" . $db->escape( $value ) . "')";
		}
		if( !empty( $query ) ) {
			$db->query( $query );
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

