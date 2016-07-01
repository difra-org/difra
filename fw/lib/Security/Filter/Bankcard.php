<?php

namespace Difra\Security\Filter;

/**
 * Class Bankcard
 * @package Difra\Security\Filter
 */
class Bankcard implements Common
{
    /**
     * Validate input string
     * @param string $string
     * @return bool
     */
    public static function validate($string)
    {
        return (bool)\Drafton\Libs\Bankcard::getType($string);
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
        return str_replace([' ', '-'], '', $string);
    }
}
