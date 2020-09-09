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
    const VERSION = '7.0.0-alpha6';
    private static $compatibility = 0;

    /**
     * Get site or framework version
     * @return string
     */
    public static function getBuild(bool $short = false)
    {
//        static $revision = null;
//        if (!is_null($revision)) {
//            return $revision;
//        }
        if ($revision = Config::getInstance()->get('version')) {
        } else {
            $revision = self::VERSION;
        }
        if (!$short && !Envi::isProduction()) {
            static $time = null;
            if (!$time) {
                $time = time();
            }
            $revision .= '/' . $time;
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

    /**
     * Get major version number
     * @return int
     */
    public static function getMajorVersion()
    {
        return (int)substr(self::VERSION, 0, strpos(self::VERSION,'.'));
    }

    /**
     * Get compatibility version
     */
    public static function getCompatibility()
    {
        if (self::$compatibility) {
            return self::$compatibility;
        }
        $instanceCfg = Config::getInstance()->getValue('instances', \Difra\View::$instance);
        return self::$compatibility = ($instanceCfg['compatibility'] ?? self::getMajorVersion());
    }

    /**
     * Set compatibility version
     * @param int $compatibility
     */
    public static function setCompatibility($compatibility)
    {
        self::$compatibility = $compatibility;
    }
}
