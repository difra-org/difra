<?php

namespace Difra;

use Difra\DB\Adapters\MySQL;
use Difra\DB\Adapters\Sqlite;

/**
 * Factory for DB
 * Class DB
 * @package Difra
 */
class DB
{
    /** @var array Configuration instances */
    private static $adapters = [];

    /**
     * @param string $instance
     * @return MySQL|Sqlite
     * @throws \Difra\DB\Exception
     */
    public static function getInstance($instance = 'default')
    {
        if (isset(self::$adapters[$instance])) {
            // TODO: ping db
            return self::$adapters[$instance];
        }

        $cfg = self::getConfig();
        if (!isset($cfg[$instance]) and $instance != 'default') {
            return self::$adapters[$instance] = self::getInstance();
        }
        switch (strtolower($cfg[$instance]['type'])) {
            case 'mysql':
                return self::$adapters[$instance] = new MySQL($cfg[$instance]);
            case 'sqlite':
                return self::$adapters[$instance] = new Sqlite($cfg[$instance]);
            default:
                throw new \Difra\DB\Exception("PDO adapter not found for '{$cfg[$instance]['type']}'");
        }
    }

    /**
     * Get configuration
     * @return mixed
     */
    private static function getConfig()
    {
        static $cfg = null;
        if (!is_null($cfg)) {
            return $cfg;
        }

        return $cfg = Config::getInstance()->get('db');
    }

    /**
     * Create set string from array keys
     * Example:
     * ['key1' => 'value1', 'key2' => 'value2'] converts to "`key1`=:key1,`key2`=:key2"
     * Warning: keys are not escaped!
     *
     * @param $array
     * @return string
     */
    public static function getSetKeys($array) : string
    {
        $set = [];
        foreach ($array as $k=>$v) {
            $set[] = "`$k`=:$k";
        }
        return implode(',', $set);
    }
}
