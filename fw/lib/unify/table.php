<?php

namespace Difra\Unify;

class Table extends Storage {

	/** @var array[string $name] */
	static protected $propertiesList = null;
	/** @var Имя Property с Primary Key */
	static protected $primary = null;

	/**
	 * Возвращает имя таблицы
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
	 * Разбивает имя класса с неймспейсом на части и убирает лишнее
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
	 * Возвращает имя столбца с primary key или массив, если primary key состоит из нескольких столбцов
	 *
	 * @return string
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

	/**
	 * Получение статуса таблицы объекта
	 * @return array
	 */
	public static function getObjDbStatus() {

		// handle empty object
		if( empty( static::$propertiesList ) ) {
			return array( 'status' => 'error' );
		}

		$table = static::getTable();
		$db = \Difra\MySQL::getInstance();
		// check if table exists
		try {
			$current = $db->fetch( "DESC `" . $db->escape( $table ) . "`" );
		} catch( \Difra\Exception $ex ) {
			return array( 'status' => 'missing', 'name' => $table );
		}

		// compare columns
		$currentColumns = array();
		foreach( $current as $line ) {
			$currentColumns[$line['Field']] = $line;
		}
		$goalColumns = static::getColumns();
		$previousColumn = null;
		while( true ) {
			$goal = each( $goalColumns );
			$goalName = $goal ? $goal['key'] : false;
			$goalColumn = $goal ? $goal['value'] : false;
			$current = each( $currentColumns );
			$currentColumn = $current ? $current['value'] : false;
			$currentName = $currentColumn ? $currentColumn['Field'] : false;

			if( $goalColumn === false and $currentColumn === false ) {
				break;
			}
			if( $goalColumn === false ) {
				// column does not exist in goal
				return array(
					'status' => 'alter',
					'action' => 'drop column',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` DROP COLUMN `' . $db->escape( $currentName ) . '`'
				);
			}
			if( !$currentName or ( $goalName != $currentName and !isset( $currentColumns[$goalName] ) ) ) {
				// goal column does not exist in db
				return array(
					'status' => 'alter',
					'action' => 'add column',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` ADD COLUMN ' . self::getColumnDefinition(
						$goalName,
						$goalColumn
					) . ( $previousColumn ? ' AFTER `' . $db->escape( $previousColumn ) . '`' : ' FIRST' )
				);
			};
			if( $goalName != $currentName ) {
				// column exists in wrong place
				return array(
					'status' => 'alter',
					'action' => 'move column',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` MODIFY COLUMN ' . self::getColumnDefinition(
						$goalName,
						$goalColumn
					) . ( $previousColumn ? ' AFTER `' . $db->escape( $previousColumn ) . '`' : ' FIRST' )
				);
			};
			if( static::getColumnDefinitionFromDesc( $currentColumn ) != static::getColumnDefinition( $goalName, $goalColumn ) ) {
				// or column exists, but differs from goal
				return array(
					'status' => 'alter',
					'action' => 'modify column',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` MODIFY COLUMN ' . self::getColumnDefinition(
						$goalName,
						$goalColumn
					) . ( $previousColumn ? ' AFTER `' . $db->escape( $previousColumn ) . '`' : ' FIRST' )
				);
			};

			$previousColumn = $currentName;
		};

		// compare table keys
		$currentIndexes = static::getCurrentIndexes();
		$goalIndexes = static::getIndexes();
		$previousIndex = null;
		while( true ) {
			$goal = each( $goalIndexes );
			$goalName = $goal ? $goal['key'] : false;
			$goalIndex = $goal ? $goal['value'] : false;
			$current = each( $currentIndexes );
			$currentName = $current ? $current['key'] : false;
			$currentIndex = $current ? $current['value'] : false;

			if( $goalIndex === false and $currentIndex === false ) {
				break;
			}
			if( $goalIndex === false ) {
				// index does not exist in goal
				return array(
					'status' => 'alter',
					'action' => 'drop key',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` DROP KEY `' . $db->escape( $currentName ) . '`'
				);
			}
			if( !$currentName or ( $goalName != $currentName and !isset( $currentIndexes[$goalName] ) ) ) {
				// goal index does not exist in db
				return array(
					'status' => 'alter',
					'action' => 'add key',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` ADD ' . self::getIndexDefinition(
						$goalName,
						$goalIndex
					) . ( $previousIndex ? ' AFTER `' . $db->escape( $previousIndex ) . '`' : ' FIRST' )
				);
			};
			if( $goalName != $currentName ) {
				// index exists in wrong place
				return array(
					'status' => 'alter',
					'action' => 'move key',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` MODIFY INDEX ' . self::getIndexDefinition(
						$goalName,
						$goalIndex
					) . ( $previousIndex ? ' AFTER `' . $db->escape( $previousIndex ) . '`' : ' FIRST' )
				);
			};
			if( static::getIndexDefinitionFromDesc( $currentIndex ) != static::getIndexDefinition( $goalName, $goalIndex ) ) {
				// or index exists, but differs from goal
				echo 'current index: ' . static::getIndexDefinitionFromDesc( $currentIndex ) . "\n";
				echo 'goal index: ' . static::getIndexDefinition( $goalName, $goalIndex ) . "\n";
				return array(
					'status' => 'alter',
					'action' => 'modify key',
					'sql' => 'ALTER TABLE `' . $db->escape( $table ) . '` MODIFY INDEX ' . self::getColumnDefinition(
						$goalName,
						$goalIndex
					) . ( $previousIndex ? ' AFTER `' . $db->escape( $previousIndex ) . '`' : ' FIRST' )
				);
			};

			$previousIndex = $currentName;
		}

		return array( 'status' => 'ok' );
	}

	/**
	 * Получение статуса таблицы объекта в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public static function getObjDbStatusXML( $node ) {

		$status = self::getObjDbStatus();
		foreach( $status as $ak => $av ) {
			$node->setAttribute( $ak, $av );
		}
	}

	private static function getColumnDefinition( $name, $prop ) {

		$db = \Difra\MySQL::getInstance();
		// simple columns (name => type)
		if( !is_array( $prop ) ) {
			return '`' . $db->escape( $name ) . '` ' . $prop;
		}
		// column name
		$line = '`' . $db->escape( $name ) . '` ' . $prop['type'];
		// length
		$line .= !empty( $prop['length'] ) ? "({$prop['length']})" : self::getDefaultValueForSqlType( $prop['type'] );
		// default value
		if( !empty( $prop['default'] ) ) {
			$line .= " DEFAULT {$prop['default']}";
		} elseif( !empty( $prop['required'] ) and $prop['required'] ) {
			$line .= ' NOT NULL';
		}
		// column options
		empty( $prop['options'] ) ? :
			$line .= ' ' . ( is_array( $prop['options'] ) ? implode( ' ', $prop['options'] ) : $prop['options'] );
		return $line;
	}

	private static function getColumnDefinitionFromDesc( $desc ) {

		$db = \Difra\MySQL::getInstance();
		$line = '`' . $db->escape( $desc['Field'] ) . '` ' . $desc['Type'];
		if( $desc['Default'] ) {
			$line .= ' DEFAULT ' . $desc['Default'];
		} elseif( $desc['Null'] == 'NO' and static::getPrimary() != $desc['Field'] ) {
			$line .= ' NOT NULL';
		}
		if( $desc['Extra'] ) {
			$line .= ' ' . $desc['Extra'];
		}
		return $line;
	}

	private static function getIndexDefinition( $name, $prop ) {

		switch( $prop['type'] ) {
			case 'unique':
				return 'UNIQUE KEY `' . $name . '` (`' . implode( '`,`', $prop['columns'] ) . '`)';
			case 'index':
				return 'KEY `' . $name . '` (`' . implode( '`,`', $prop['columns'] ) . '`)';
			case 'primary':
				return 'PRIMARY KEY `' . $name . '` (`' . implode( '`,`', (array)$prop['columns'] ) . '`)';
			case 'fulltext':
				return 'FULLTEXT KEY `' . $name . '` (`' . implode( '`,`', $prop['columns'] ) . '`)';
//			case 'foreign':
//				/** @var Item $targetObj */
//				$targetObj = Storage::getClass( $prop['target'] );
//				return 'CONSTRAINT FOREIGN KEY `' . $name . '` (`' . implode( '`,`', $prop['columns'] ) . '`)'
//				. ' REFERENCES `' . $targetObj::getTable() . '` (`' . implode( '`,`', $prop['targets'] ) . '`)'
//				. ' ON DELETE ' . ( isset( $prop['ondelete'] ) and $prop['ondelete'] ? $prop['ondelete'] : 'CASCADE' )
//				. ' ON UPDATE ' . ( isset( $prop['onupdate'] ) and $prop['onupdate'] ? $prop['onupdate'] : 'CASCADE' );
			default:
				throw new \Difra\Exception( 'I don\'t know how to define key type ' . $prop['type'] . "\n" );
		}
	}

	private static function getCurrentIndexes() {

		$db = \Difra\MySQL::getInstance();
		$escTable = $db->escape( static::getTable() );
		$dbIndexes = $db->fetch( 'SHOW INDEXES FROM `' . $escTable . '`' );
//		$foreignKeys = $db->fetch(
//			'SELECT `constraint_name`,`ordinal_position`,`table_name`,`column_name`,`referenced_table_name`,`referenced_column_name`'
//			. ' FROM `information_schema`.`key_column_usage`'
//			. ' WHERE `referenced_table_name` IS NOT NULL AND `table_schema`=DATABASE() AND `table_name`=\'' . $escTable . '\''
//		);

		if( empty( $dbIndexes ) ) {
			return array();
		}

		$result = array();
		foreach( $dbIndexes as $row ) {
			if( !isset( $result[$row['Key_name']] ) ) {
				if( $row['Key_name'] = 'PRIMARY' ) {
					$type = 'primary';
				} elseif( $row['Non_unique'] == '0' ) {
					$type = 'unique';
				} elseif( $row['Index_type'] == 'FULLTEXT' ) {
					$type = 'fulltext';
				} else {
					$type = 'index';
				}
				$result[$row['Key_name']] = array(
					'type' => $type,
					'columns' => array(
						$row['Seq_in_index'] => $row['Column_name']
					)
				);
			} else {
				$result[$row['Key_name']]['columns'][$row['Seq_in_index']] = $row['Column_name'];
			}
		}
		return $result;
	}

	private static function getDefaultValueForSqlType( $type ) {

		switch( $type ) {
			case 'int':
				return '(11)';
			default:
				return '';
		}
	}

	/**
	 * Получение строки для создания таблицы
	 * @throws \Difra\Exception
	 * @return string
	 */
	public static function getDbCreate() {

		if( empty( static::$propertiesList ) ) {
			throw new \Difra\Exception( 'Can\'t create table for empty object.' );
		}
		$columns = array();
		$indexes = array();
		if( $createPrimary = static::getCreatePrimary() ) {
			$indexes[] = $createPrimary;
		}
		foreach( static::getColumns() as $name => $prop ) {
			$lines[] = self::getColumnDefinition( $name, $prop );
		}
		foreach( static::getIndexes() as $name => $prop ) {
			$indexes[] = self::getIndexDefinition( $name, $prop );
		}
		$lines = array_merge( $columns, $indexes );
		$create = 'CREATE TABLE `' . static::getTable() . "` (\n" . implode( ",\n", $lines ) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		return $create;
	}

	static private $keyTypes = array(
		'index',
		'primary',
		'unique',
		'fulltext',
		'foreign'
	);

	/**
	 * Возвращает список всех записей в self::$propertiesList, которые описывают строки
	 *
	 * @return array
	 */
	private static function getColumns() {

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
	 * Возвращает список всех записей в self::$propertiesList, которые описывают индексы
	 *
	 * @return array
	 */
	private static function getIndexes() {

		$result = null;
		if( !is_null( $result ) ) {
			return $result;
		}
		$result = array();
		if( $primary = static::getPrimary() ) {
			$result['primary'] = array( 'type' => 'primary', 'columns' => $primary );
		}
		foreach( static::$propertiesList as $name => $prop ) {
			if( !is_array( $prop ) ) {
			} elseif( in_array( $prop['type'], self::$keyTypes ) ) {
				$result[$name] = $prop;
			} else {
				foreach( self::$keyTypes as $keyType ) {
					if( isset( $prop[$keyType] ) and $prop[$keyType] ) {
						$result[$name] = array( 'type' => $keyType, 'columns' => $name );
						break;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Возвращает строку для создания Primary Key
	 *
	 * @return bool|string
	 */
	private static function getCreatePrimary() {

		if( !$primary = static::getPrimary() ) {
			return false;
		}

		return '  PRIMARY KEY (`' . implode( '`,`', (array)$primary ) . '`)';
	}

	/**
	 * Создание таблицы для объекта
	 */
	public static function createDb() {

		\Difra\MySQL::getInstance()->query( self::getDbCreate() );
	}
}