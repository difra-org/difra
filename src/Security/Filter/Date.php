<?php

namespace Difra\Security\Filter;

/**
 * Class Date
 * @package Difra\Security\Filter
 */
class Date implements Common
{
    /** @var \Datetime[] */
    private static $cache = [];

    /**
     * Validate input string
     * @param string $string
     * @return bool
     */
    public static function validate($string)
    {
        if (!isset(self::$cache[$string])) {
            self::$cache[$string] = self::parse($string) ?: false;
        }
        return self::$cache[$string] ? true : false;
    }

    /**
     * Sanitize input string
     * @param string $string
     * @return string
     */
    public static function sanitize($string)
    {
        if (!self::validate($string)) {
            return null;
        }
        return self::$cache[$string]->format('Y-m-d');
    }

    /**
     * Parse custom date
     * @param $string
     * @return \Datetime|null
     */
    private static function parse($string)
    {
        try {
            $dt = new \Datetime($string);
            if ($dt) {
                return $dt;
            }
        } catch (\Exception $e) {
        }
        return null;
    }
}
