<?php

namespace Difra\Unify;

use Difra\Exception;
use Difra\MySQL;

/**
 * Class DBAPI
 * @package Difra\Unify
 */
class DBAPI extends Table
{
    /**
     * Get status of database table for Unify Object
     * @return array
     */
    public static function getObjDbStatus()
    {
        // handle empty object
        if (empty(static::$propertiesList)) {
            return ['status' => 'error'];
        }

        $table = static::getTable();
        $db = MySQL::getInstance();
        // check if table exists
        try {
            $current = $db->fetch("DESC `" . $db->escape($table) . "`");
        } catch (Exception $ex) {
            return ['status' => 'missing', 'name' => $table];
        }

        // compare columns
        $currentColumns = [];
        foreach ($current as $line) {
            $currentColumns[$line['Field']] = $line;
        }
        $goalColumns = static::getColumns();
        $previousColumn = null;
        while (true) {
            $goal = each($goalColumns);
            $goalName = $goal ? $goal['key'] : false;
            $goalColumn = $goal ? $goal['value'] : false;
            $current = each($currentColumns);
            /** @var array|bool $currentColumn */
            $currentColumn = $current ? $current['value'] : false;
            $currentName = $currentColumn ? $currentColumn['Field'] : false;

            if ($goalColumn === false and $currentColumn === false) {
                break;
            }
            if ($goalColumn === false) {
                // column does not exist in goal
                return [
                    'status' => 'alter',
                    'action' => 'drop column',
                    'sql' =>
                        'ALTER TABLE `' .
                        $db->escape($table) .
                        '` DROP COLUMN `' .
                        $db->escape($currentName) .
                        '`'
                ];
            }
            if (!$currentName or ($goalName != $currentName and !isset($currentColumns[$goalName]))) {
                // goal column does not exist in db
                return [
                    'status' => 'alter',
                    'action' => 'add column',
                    'sql' => 'ALTER TABLE `' . $db->escape($table) . '` ADD COLUMN ' . self::getColumnDefinition(
                            $goalName,
                            $goalColumn
                        ) . ($previousColumn ? ' AFTER `' . $db->escape($previousColumn) . '`' : ' FIRST')
                ];
            };
            if ($goalName != $currentName) {
                // column exists in wrong place
                return [
                    'status' => 'alter',
                    'action' => 'move column',
                    'sql' => 'ALTER TABLE `' . $db->escape($table) . '` MODIFY COLUMN ' . self::getColumnDefinition(
                            $goalName,
                            $goalColumn
                        ) . ($previousColumn ? ' AFTER `' . $db->escape($previousColumn) . '`' : ' FIRST')
                ];
            };
            if (static::getColumnDefinitionFromDesc($currentColumn) !=
                static::getColumnDefinition($goalName, $goalColumn)
            ) {
                // column exists, but differs from goal
                return [
                    'status' => 'alter',
                    'action' => 'modify column',
                    'sql' => 'ALTER TABLE `' . $db->escape($table) . '` MODIFY COLUMN ' . self::getColumnDefinition(
                            $goalName,
                            $goalColumn
                        ) . ($previousColumn ? ' AFTER `' . $db->escape($previousColumn) . '`' : ' FIRST')
                ];
            };

            $previousColumn = $currentName;
        };

        // compare table keys
        $currentIndexes = static::getCurrentIndexes();
        $goalIndexes = static::getIndexes();
        $previousIndex = null;
        if (!empty($currentIndexes)) {
            foreach ($currentIndexes as $currentName => $currentIndex) {
                if (!isset($goalIndexes[$currentName])) {
                    // index does not exist in goal
                    return [
                        'status' => 'alter',
                        'action' => 'drop key',
                        'sql' =>
                            'ALTER TABLE `' .
                            $db->escape($table) .
                            '` DROP KEY `' .
                            $db->escape($currentName) .
                            '`'
                    ];
                }
            }
        }
        if (!empty($goalIndexes)) {
            foreach ($goalIndexes as $goalName => $goalIndex) {

                if (!isset($currentIndexes[$goalName])) {
                    // goal index does not exist in db
                    return [
                        'status' => 'alter',
                        'action' => 'add key',
                        'sql' => 'ALTER TABLE `' . $db->escape($table) . '` ADD ' . self::getIndexDefinition(
                                $goalName,
                                $goalIndex
                            )
                    ];
                };

                if (
                    static::getIndexDefinition($goalName, $goalIndex) !=
                    static::getIndexDefinition($goalName, $currentIndexes[$goalName])
                ) {
                    // index exists, but differs from goal
                    return [
                        'status' => 'alter',
                        'action' => 'drop key',
                        'sql' =>
                            'ALTER TABLE `' .
                            $db->escape($table) .
                            '` DROP KEY `' .
                            $db->escape($goalName) .
                            '`'
                    ];
                };
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Get status of database table in XML
     * @param \DOMElement|\DOMNode $node
     */
    public static function getObjDbStatusXML($node)
    {
        $status = self::getObjDbStatus();
        foreach ($status as $ak => $av) {
            $node->setAttribute($ak, $av);
        }
    }

    /**
     * Get string for Primary Key create/alter
     * @return bool|string
     */
    private static function getCreatePrimary()
    {
        if (!$primary = static::getPrimary()) {
            return false;
        }

        return '  PRIMARY KEY (`' . implode('`,`', (array)$primary) . '`)';
    }

    /**
     * Create database table
     */
    public static function createDb()
    {
        MySQL::getInstance()->query(self::getDbCreate());
    }

    /**
     * Alias types
     * @var array[]
     */
    private static $typeAliases = [
        'bool' => [
            'type' => 'tinyint',
            'length' => 1
        ],
        'boolean' => [
            'type' => 'tinyint',
            'length' => 1
        ]
    ];

    /**
     * Preprocess $propertiesList definitions
     * @param mixed $prop
     * @return array
     */
    private static function preprocessDefinition($prop)
    {
        // simple columns (name => type)
        if (!is_array($prop)) {
            $prop = ['type' => $prop];
        }
        // type aliases
        if (!empty(self::$typeAliases[$prop['type']])) {
            foreach (self::$typeAliases[$prop['type']] as $k => $v) {
                $prop[$k] = $v;
            }
        }
        return $prop;
    }

    /**
     * Generates SQL string for column create/alter
     * @param string $name Column name
     * @param string|array $prop Type or properties array
     * @return string
     */
    private static function getColumnDefinition($name, $prop)
    {
        $prop = self::preprocessDefinition($prop);
        $db = MySQL::getInstance();
        $line =
            '`' .
            $db->escape($name) .
            '` '
            // column name
            .
            $prop['type']
            // type
            .
            (!empty($prop['length']) ? "({$prop['length']})" : self::getDefaultSizeForSqlType($prop['type']))
            // length
            .
            ((!empty($prop['unsigned']) and $prop['unsigned']) ? ' unsigned' : ''); // unsigned
        // default value
        if (!empty($prop['default'])) {
            $line .= self::getDefault($prop['default']);
        } elseif ((!empty($prop['required']) and $prop['required']) or (!empty($prop['null']) and !$prop['null'])) {
            $line .= ' NOT NULL';
        }
        // column options
        empty($prop['options'])
            ?:
            $line .= ' ' . (is_array($prop['options']) ? implode(' ', $prop['options']) : $prop['options']);
        return $line;
    }

    /**
     * Generates SQL string for column create/alter
     * @param array $desc Row from DESC `table` answer
     * @return string
     */
    private static function getColumnDefinitionFromDesc($desc)
    {
        $db = MySQL::getInstance();
        $line = '`' . $db->escape($desc['Field']) . '` ' . $desc['Type'];
        if ($desc['Default']) {
            $line .= self::getDefault($desc['Default']);
        } elseif ($desc['Null'] == 'NO' and static::getPrimary() != $desc['Field']) {
            $line .= ' NOT NULL';
        }
        if ($desc['Extra']) {
            $line .= ' ' . $desc['Extra'];
        }
        return $line;
    }

    private static function getDefault($value)
    {
        static $defaultKeywords = ['CURRENT_TIMESTAMP'];
        if (in_array(mb_strtoupper($value), $defaultKeywords)) {
            return ' DEFAULT ' . $value;
        } else {
            return " DEFAULT '{$value}'";
        }
    }

    /**
     * Generates SQL string for key create/alter
     * @param $name
     * @param $prop
     * @return string
     * @throws Exception
     */
    private static function getIndexDefinition($name, $prop)
    {
        switch ($prop['type']) {
            case 'unique':
                return 'UNIQUE KEY `' . $name . '` (`' . implode('`,`', (array)$prop['columns']) . '`)';
            case 'index':
                return 'KEY `' . $name . '` (`' . implode('`,`', (array)$prop['columns']) . '`)';
            case 'primary':
                return 'PRIMARY KEY (`' . implode('`,`', (array)$prop['columns']) . '`)';
            case 'fulltext':
                return 'FULLTEXT KEY `' . $name . '` (`' . implode('`,`', (array)$prop['columns']) . '`)';
            case 'foreign':
                /** @var Item $targetObj */
                $targetTable =
                    ($targetObj = Storage::getClass($prop['target'])) ? $targetObj::getTable() : $prop['target'];
                return
                    'CONSTRAINT `' .
                    $name .
                    '`'
                    .
                    ' FOREIGN KEY (`' .
                    implode('`,`', (array)$prop['source']) .
                    '`)'
                    .
                    ' REFERENCES `' .
                    $targetTable .
                    '` (`' .
                    implode('`,`', (array)$prop['keys']) .
                    '`)'
                    .
                    ' ON DELETE ' .
                    ((isset($prop['ondelete']) and $prop['ondelete']) ? $prop['ondelete'] : 'CASCADE')
                    .
                    ' ON UPDATE ' .
                    ((isset($prop['onupdate']) and $prop['onupdate']) ? $prop['onupdate'] : 'CASCADE');
            default:
                throw new Exception('I don\'t know how to define key type ' . $prop['type'] . "\n");
        }
    }

    /**
     * Get keys from current database table
     * @return array
     */
    private static function getCurrentIndexes()
    {
        $db = MySQL::getInstance();
        $escTable = $db->escape(static::getTable());
        $dbIndexes = $db->fetch('SHOW INDEXES FROM `' . $escTable . '`');
        $foreignKeys = $db->fetch(
            'SELECT `constraint_name`,`column_name`,`referenced_table_name`,`referenced_column_name`'
            .
            ' FROM `information_schema`.`key_column_usage`'
            .
            ' WHERE `referenced_table_name` IS NOT NULL AND `table_schema`=DATABASE() AND `table_name`=\'' .
            $escTable .
            '\''
            .
            ' ORDER BY `ordinal_position`'
        );
        $result = [];

        // regular indexes
        if (!empty($dbIndexes)) {
            foreach ($dbIndexes as $row) {
                if (!isset($result[$row['Key_name']])) {
                    if ($row['Key_name'] == 'PRIMARY') {
                        $type = 'primary';
                    } elseif ($row['Non_unique'] == '0') {
                        $type = 'unique';
                    } elseif ($row['Index_type'] == 'FULLTEXT') {
                        $type = 'fulltext';
                    } else {
                        $type = 'index';
                    }
                    $result[$row['Key_name']] = [
                        'type' => $type,
                        'columns' => [
                            $row['Seq_in_index'] => $row['Column_name']
                        ]
                    ];
                } else {
                    $result[$row['Key_name']]['columns'][$row['Seq_in_index']] = $row['Column_name'];
                }
            }
        }

        // foreign keys
        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $foreign) {
                if (!isset($result[$foreign['constraint_name']])) {
                    $result[$foreign['constraint_name']] = [
                        'type' => 'foreign',
                        'source' => (array)$foreign['column_name'],
                        'target' => $foreign['referenced_table_name'],
                        'keys' => (array)$foreign['referenced_column_name']
                    ];
                } else {
                    $result[$foreign['constraint_name']]['column_name'][] = $foreign['column_name'];
                    $result[$foreign['constraint_name']]['referenced_column_name'][] =
                        $foreign['referenced_column_name'];
                }
            }
        }
        return $result;
    }

    /**
     * Returns default size for SQL type, e.g. f('int') == (11)
     * @param string $type
     * @return string
     */
    private static function getDefaultSizeForSqlType($type)
    {
        switch ($type) {
            case 'int':
                return '(11)';
            default:
                return '';
        }
    }

    /**
     * Get string for CREATE TABLE SQL command
     * @throws Exception
     * @return string
     */
    public static function getDbCreate()
    {
        if (empty(static::$propertiesList)) {
            throw new Exception('Can\'t create table for empty object.');
        }
        $lines = [];
        $indexes = [];
//		if( $createPrimary = static::getCreatePrimary() ) {
//			$indexes[] = $createPrimary;
//		}
        foreach (static::getColumns() as $name => $prop) {
            $lines[] = self::getColumnDefinition($name, $prop);
        }
        foreach (static::getIndexes() as $name => $prop) {
            $indexes[] = self::getIndexDefinition($name, $prop);
        }
        $lines = array_merge($lines, $indexes);
        $create =
            'CREATE TABLE `' .
            static::getTable() .
            "` (\n" .
            implode(",\n", $lines) .
            "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        return $create;
    }

    /**
     * Get tables state as XML
     * @param \DOMElement|\DOMNode $node
     */
    final public static function getDbStatusXML($node)
    {
        if (empty(self::$classes)) {
            $node->setAttribute('empty', 1);
            return;
        }
        foreach (self::$classes as $objKey => $className) {
            $objNode = $node->appendChild($node->ownerDocument->createElement($objKey));
            /** @var DBAPI $className */
            $className::getObjDbStatusXML($objNode);
        }
    }
}
