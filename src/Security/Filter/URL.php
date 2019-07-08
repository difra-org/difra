<?php

namespace Difra\Security\Filter;

class URL implements Common
{
    /**
     * @inheritdoc
     */
    public static function validate($string)
    {
        return filter_var($string, FILTER_VALIDATE_URL);
    }

    /**
     * @inheritdoc
     */
    public static function sanitize($string)
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }
}