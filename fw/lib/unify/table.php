<?php

namespace Difra\Unify;

class Table extends Storage {

	/** @var array[string $name] Object properties definitions */
	static protected $propertiesList = null;
	/** @var string|string[] Column or column list for Primary Key */
	static protected $primary = null;

	/**
	 * Returns table name
	 *
	 * @return string
	 */
	public static function getTable() {

		static $table = null;
		if( !is_null( $table ) ) {
			return $table;
		}
		return $table = mb_strtolower( implode( '_', static::getClassParts() ) );
	}

	/**
	 * Chops namespace and class into parts without common pieces
	 *
	 * @return array
	 * @throws \Difra\Exception
	 */
	protected static function getClassParts() {

		static $parts = null;
		if( !is_null( $parts ) ) {
			return $parts;
		}
		$parts = explode( '\\', $class = get_called_class() );
		if( sizeof( $parts ) < 4 or $parts[0] != 'Difra' or $parts[1] != 'Plugins' or $parts[3] != 'Objects' ) {
			throw new \Difra\Exception( 'Bad object class name: ' . $class );
		}
		unset( $parts[3] );
		unset( $parts[1] );
		unset( $parts[0] );
		return $parts;
	}

	/**
	 * Returns column name or list of column names for Primary Key
	 *
	 * @return string|string[]
	 */
	public static function getPrimary() {

		static $primary = null;
		if( !is_null( $primary ) ) {
			return $primary;
		}
		if( static::$primary ) {
			return $primary = static::$primary;
		}
		if( !empty( static::$propertiesList ) ) {
			foreach( static::$propertiesList as $name => $desc ) {
				if( !is_array( $desc ) ) {
					continue;
				}
				if( isset( $desc['primary'] ) and $desc['primary'] ) {
					return $primary = $name;
				}
			}
		}
		return $primary = false;
	}

	/** @var string[] List of supported key types */
	static private $keyTypes = array(
		'index',
		'primary',
		'unique',
		'fulltext',
		'foreign'
	);

	/**
	 * Get list of columns from self::$propertiesList
	 *
	 * @return array
	 */
	protected static function getColumns() {

		static $result = null;
		if( !is_null( $result ) ) {
			return $result;
		}
		$result = array();
		foreach( static::$propertiesList as $name => $prop ) {
			$type = !is_array( $prop ) ? $prop : $prop['type'];
			if( !in_array( $type, self::$keyTypes ) ) {
				$result[$name] = $prop;
			}
		}
		return $result;
	}

	/**
	 * Get list of indexes from self::$propertiesList
	 *
	 * @return array
	 */
	protected static function getIndexes() {

		$result = null;
		if( !is_null( $result ) ) {
			return $result;
		}
		$result = array();
		if( $primary = static::getPrimary() ) {
			$result['PRIMARY'] = array( 'type' => 'primary', 'columns' => $primary );
		}
		foreach( static::$propertiesList as $name => $prop ) {
			if( !is_array( $prop ) ) {
			} elseif( in_array( $prop['type'], self::$keyTypes ) ) {
				$result[$name] = array( 'type' => $prop['type'], 'columns' => isset( $prop['columns'] ) ? $prop['columns'] : $name );
			} else {
				foreach( self::$keyTypes as $keyType ) {
					if( $keyType == 'primary' ) {
						continue;
					}
					if( isset( $prop[$keyType] ) and $prop[$keyType] ) {
						$result[$name] = array( 'type' => $keyType, 'columns' => $name );
						break;
					}
				}
			}
		}
		return $result;
	}

	public static function getPropertiesList() {

		return self::$propertiesList;
	}
}