<?php

namespace Difra\Libs;

include_once(DIR_FW . 'lib/Libs/ESAPI/trunk/src/ESAPI.php');

/**
 * Class ESAPI
 * @package Difra\Libs
 * @deprecated
 */
class ESAPI
{
    /**
     * @return \ESAPI
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new \ESAPI(DIR_FW . 'lib/libs/esapi/ESAPI.xml');
    }

    /**
     * @return \Validator
     */
    public static function validator()
    {
        return self::getInstance()->getValidator();
    }

    /**
     * @return \Encoder
     */
    public static function encoder()
    {
        return self::getInstance()->getEncoder();
    }

    /**
     * Validate URL
     * @param $url
     * @return bool
     */
    public static function validateURL($url)
    {
        try {
            return @self::getInstance()->getValidator()->isValidInput("URLContext", $url, "URL", 255, false);
        } catch (\Exception $ex) {
            return false;
        }
    }
}
