<?php

namespace Difra\Envi;

use Difra\Config;
use Difra\Envi;

/**
 * Class Version
 * @package Difra\Envi
 */
class Version
{
    /** Framework version */
    const VERSION = '7.0.0-alpha3';

    /**
     * Get site or framework version
     * @return string
     */
    public static function getBuild()
    {
        static $revision = null;
        if (!is_null($revision)) {
            return $revision;
        }
        if ($revision = Config::getInstance()->get('version')) {
        } else {
            $revision = self::VERSION;
        }
        if (!Envi::isProduction()) {
            $revision .= '/' . time();
        }
        return $revision;
    }

    /**
     * Get framework version
     * @return string
     */
    public static function getFrameworkVersion()
    {
        return self::VERSION;
    }

}
