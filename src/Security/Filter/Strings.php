<?php

namespace Difra\Security\Filter;

class Strings implements Common
{
    /**
     * @inheritdoc
     */
    public static function validate($string)
    {
        return is_string($string);
    }

    /**
     * @inheritdoc
     */
    public static function sanitize($string)
    {
        return (string)$string;
    }
}