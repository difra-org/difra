<?php

namespace Difra\Unify;

use Difra\Exception;

/**
 * Class Storage
 * @package Difra\Unify
 */
abstract class Storage
{
    /** @var Object[string $objKey][id] */
    static public $objects = [];
    /** @var array List of available Unify Objects classes as name=>object */
    static protected $classes = [];

    /**
     * Register object(s)
     * @param string[] $list
     */
    final public static function registerObjects($list)
    {
        if (!$list or empty($list)) {
            return;
        }
        if (!is_array($list)) {
            $list = [$list];
        }
        /** @var $class Item */
        foreach ($list as $class) {
            self::$classes[$class::getObjKey()] = $class;
        }
    }

    /**
     * Get object by name and primary key
     * @param string $objKey
     * @param mixed $primary
     * @return mixed
     * @throws Exception
     */
    final public static function getObj($objKey, $primary)
    {
        $class = self::getClass($objKey);
        if (!$class) {
            throw new Exception("Can't find class for object '{$objKey}''");
        }
        return $class::get($primary);
    }

    /**
     * Get Unify Object class by name
     * @param $objKey
     * @return string|Item|null
     */
    final public static function getClass($objKey)
    {
        return isset(self::$classes[$objKey]) ? '\\' . self::$classes[$objKey] : null;
    }

    /**
     * Create new item object by name
     * @param string $objKey
     * @return static
     * @throws Exception
     */
    final public static function createObj($objKey)
    {
        $class = self::getClass($objKey);
        if (!$class) {
            throw new Exception("Can't find class for object '{$objKey}''");
        }
        return $class::create();
    }

    /**
     * Get all Unify Object classes list
     * @return array
     */
    final public static function getAllClasses()
    {
        return self::$classes;
    }
}
