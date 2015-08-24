<?php

namespace Difra\MySQL\SQL;

/**
 * Class Common
 *
 * @package Difra\MySQL\SQL
 * @deprecated
 */
abstract class Common
{
    /** @var self[] If objects are named (e.g. tables), store them here */
    protected static $list = [];
    /**
     * Create object from chopped SQL
     *
     * @param array $chunks
     * @return mixed
     */
    //abstract public static function create( $chunks = null );

    /**
     * Get list of loaded objects
     *
     * @return array
     */
    public static function getList()
    {
        return self::$list;
    }

    /**
     * Get name from chunk
     *
     * @param $name
     * @return string
     */
    public static function chunk2name($name)
    {
        if (mb_substr($name, 0, 1) == '`' and mb_substr($name, -1) == '`') {
            $name = mb_substr($name, 1, strlen($name) - 2);
        }
        return $name;
    }
}