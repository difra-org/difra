<?php

namespace Difra\Security\Filter;

class Floats implements Common
{
    /**
     * @inheritdoc
     */
    public static function validate($string)
    {
        $string = str_replace(',', '.', $string);
        return (false !== filter_var($string, FILTER_VALIDATE_FLOAT)) ? true : false;
    }

    /**
     * @inheritdoc
     */
    public static function sanitize($string)
    {
        $string = str_replace(',', '.', $string);
        return filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}