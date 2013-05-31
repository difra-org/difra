<?php

namespace Difra;

use Difra\MySQL\Abstracts\MySQLi;
use Difra\MySQL\Abstracts\None;

/**
 * Абстрактная фабрика для MySQL-адаптеров
 * Class MySQL
 *
 * @package Difra
 */
class MySQL {

	const INST_AUTO = 'auto';
	const INST_MySQLi = 'MySQLi';
	const INST_NONE = 'none';
	const INST_DEFAULT = self::INST_AUTO;

	private static $adapters = array();

	/**
	 * @param string $adapter
	 * @return MySQL\Abstracts\MySQLi|MySQL\Abstracts\None
	 */
	public static function getInstance( $adapter = self::INST_DEFAULT ) {

		if( $adapter == self::INST_AUTO ) {
			static $auto = null;
			if( !is_null( $auto ) ) {
				return self::getInstance( $auto );
			}

			if( MySQLi::isAvailable() ) {
				Debugger::getInstance()->addLine( "MySQL module: MySQLi" );
				return self::getInstance( $auto = self::INST_MySQLi );
			} else {
				Debugger::getInstance()->addLine( "No suitable MySQL module detected" );
				return self::getInstance( $auto = self::INST_NONE );
			}
		}

		if( isset( self::$adapters[$adapter] ) ) {
			return self::$adapters[$adapter];
		}

		switch( $adapter ) {
		case self::INST_MySQLi:
			self::$adapters[$adapter] = new MySQLi();
			return self::$adapters[$adapter];
		default:
			if( !isset( self::$adapters[self::INST_NONE] ) ) {
				self::$adapters[self::INST_NONE] = new None();
			}
			return self::$adapters[self::INST_NONE];
		}
	}
}
