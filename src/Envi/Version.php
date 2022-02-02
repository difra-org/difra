<?php

declare(strict_types=1);

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
    protected const VERSION = '8.0';

    /**
     * Get site or framework version
     * @param bool $short
     * @return string
     */
    public static function getBuild(bool $short = false): string
    {
        static $revision = null;
        if (is_null($revision)) {
            $revision = Config::getInstance()->get('version') ?: self::VERSION;
        }
        if (!$short && !Envi::isProduction()) {
            static $shortRevision = null;
            if (is_null($shortRevision)) {
                $shortRevision = $revision . '/' . time();
            }
            return $shortRevision;
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
}
