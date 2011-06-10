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

}	