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

	/** Автоматическое определение адатера */
	const INST_AUTO = 'auto';
	/** MySQLi */
	const INST_MySQLi = 'MySQLi';
	/** Заглушка на случай, когда не доступно ни одного адаптера */
	const INST_NONE = 'none';
	/** Адаптер по умолчанию */
	const INST_DEFAULT = self::INST_AUTO;

	private static $adapters = array();

	/**
	 * @param string $adapter
	 * @param bool   $new
	 *
	 * @return MySQL\Abstracts\MySQLi|MySQL\Abstracts\None
	 */
	public static function getInstance( $adapter = self::INST_DEFAULT, $new = false ) {

		if( $adapter == self::INST_AUTO ) {
			static $auto = null;
			if( !is_null( $auto ) ) {
				return self::getInstance( $auto, $new );
			}

			if( MySQLi::isAvailable() ) {
				Debugger::addLine( "MySQL module: MySQLi" );
				return self::getInstance( $auto = self::INST_MySQLi, $new );
			} else {
				Debugger::addLine( "No suitable MySQL module detected" );
				return self::getInstance( $auto = self::INST_NONE, $new );
			}
		}

		if( !$new and isset( self::$adapters[$adapter] ) ) {
			return self::$adapters[$adapter];
		}

		switch( $adapter ) {
		case self::INST_MySQLi:
			$obj = new MySQLi();
			break;
		default:
			$obj = new None();
		}
		if( !$new ) {
			self::$adapters[$adapter] = $obj;
		}
		return $obj;
	}
}
