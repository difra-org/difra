<?php

namespace Difra\Plugins\SAPE;

class Links extends Common {

	const SAPE_AGENT = 'SAPE_Client PHP ';

	/**
	 * Вывод ссылок в обычном виде - текст с разделителем
	 *
	 * @return string
	 */
	public static function getHTML() {

		self::update();
		$db = \Difra\MySQL::getInstance();
		$links = $db->fetch( 'SELECT `value` FROM `sape` WHERE `key`=\'' . $db->escape( self::getUri() ) . "'" );
		if( !empty( $links ) ) {
			// TODO: после обновления на 5.5 эту прелесть ($links2) нужно заменить на array_column( $links, 'value' )
			$links2 = array();
			foreach( $links as $link ) {
				$links2[] = $link['value'];
			}
			$html = implode(
				$db->fetchOne( "SELECT `value` FROM `sape` WHERE `key`='__sape_delimiter__'" ) ?: ' ', $links2
			);
		} elseif( self::isSapeBot() ) {
			$html = $db->fetchOne( "SELECT `value` FROM `sape` WHERE `key`='__sape_new_url__'" ) ?: '';
		} else {
			$html = '';
		}
		if( $html and self::isSapeBot() ) {
			$html = '<sape_noindex>' . $html . '</sape_noindex>';
		}
		return $html;
	}

	protected static function getDispenserPath() {

		return '/code.php?user=' . self::getSapeUser() . '&host=' . self::getHost();
	}

	protected static function update() {

		$db = \Difra\MySQL::getInstance();
		$ttl = $db->fetchOne( "SELECT `value` FROM `sape` WHERE `key`='__difra_ttl__'" );
		if( $ttl and $ttl > time() ) {
			return;
		}

		$db->query( array(
				 "DELETE FROM `sape` WHERE `key`='__difra_ttl__'",
				 "INSERT INTO `sape` SET `key`='__difra_ttl__',`value`='" . $db->escape( time() + self::SAPE_RETRY ) . "'"
			    ) );
//		if( pcntl_fork() ) {
			self::save( self::fetchData() );
//			die();
//		}
	}

	protected static function save( $data ) {

		if( empty( $data ) or !is_array( $data ) ) {
			return;
		}
		$data['__difra_ttl__'] = time() + self::SAPE_TTL;
		$db = \Difra\MySQL::getInstance( \Difra\MySQL::INST_AUTO, true );
		$queries = array( 'DELETE FROM `sape`' );

		foreach( $data as $k => $v ) {
			if( empty( $v ) ) {
				continue;
			}
			if( !is_array( $v ) ) {
				$queries[] = 'INSERT INTO `sape` (`key`,`value`) VALUES (\'' . $db->escape( $k ) . "','" . $db->escape( $v ) . "')";
				continue;
			}
			foreach( $v as $v1 ) {
				$queries[] = 'INSERT INTO `sape` (`key`,`value`) VALUES (\'' . $db->escape( $k ) . "','" . $db->escape( $v1 ) . "')";
			}
		}
		$db->query( $queries );
	}
}