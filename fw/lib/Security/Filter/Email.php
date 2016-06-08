<?php

namespace Difra\Security\Filter;

class Email implements Common
{
    /**
     * Validate input string
     * @param string $string
     * @return bool
     */
    public static function validate($string)
    {
        if (mb_strpos($string, '..') !== false) {
            return false;
        }
        return (bool)preg_match(
            '/^[a-zA-Z0-9_-]([a-zA-Z0-9._-]*)+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$/',
            $string
        ) and (filter_var($string, FILTER_SANITIZE_EMAIL) !== false);
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
        return filter_var($string, FILTER_SANITIZE_EMAIL) ?: null;
    }
}
