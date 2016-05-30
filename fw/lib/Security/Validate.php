<?php

namespace Difra\Security;

class Validate
{
    /**
     * Validate e-mail address
     * @param $email
     * @return bool
     */
    public static function email($email)
    {
        if (mb_strpos($email, '..') !== false) {
            return false;
        }
        return (bool)preg_match(
            '/^[a-zA-Z0-9_-]([a-zA-Z0-9._-]*)+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$/',
            $email
        );
    }
}
