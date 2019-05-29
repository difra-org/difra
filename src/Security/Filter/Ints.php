<?php

namespace Difra\Security\Filter;

class Ints implements Common
{
    /**
     * @inheritdoc
     */
    public static function validate($string)
    {
        return filter_var($string, FILTER_VALIDATE_INT) or $string === '0' or $string === 0;
    }

    /**
     * @inheritdoc
     */
    public static function sanitize($string)
    {
        return (int)filter_var($string, FILTER_SANITIZE_NUMBER_INT);
    }

}