<?php

namespace Difra\Security\Filter;

interface Common
{
    /**
     * Validate input string
     * @param string $string
     * @return bool
     */
    public static function validate($string);

    /**
     * Sanitize input string
     * @param string $string
     * @return string
     */
    public static function sanitize($string);
}
