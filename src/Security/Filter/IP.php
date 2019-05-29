<?php

namespace Difra\Security\Filter;

class IP implements Common
{
    /**
     * @inheritdoc
     */
    public static function validate($string)
    {
        return filter_var($string, FILTER_VALIDATE_IP);
    }

    /**
     * @inheritdoc
     */
    public static function sanitize($string)
    {
        return static::validate($string) ? $string : null;
    }
}