<?php

namespace Difra\Libs;

/**
 * Class Strings
 *
 * @deprecated
 * @package Difra\Libs
 */
class Strings
{
    /**
     * Is character is a whitespace?
     *
     * @param string $char
     * @return bool
     */
    public static function isWhitespace($char)
    {
        switch ($char) {
            case "\n":
            case "\r":
            case "\t":
            case ' ':
                return true;
        }
        return false;
    }
}