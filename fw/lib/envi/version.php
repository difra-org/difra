<?php

namespace Difra\Envi;

/**
 * Class Version
 * It was Subversion revision detection for automatic cache pruning on production updates here.
 * No reason to delete this class because people who deploy from zip file will benefit from this anyways.
 * Additionally, this leaves us a way to auto-prune caches in production environments later.
 *
 * @package Difra\Envi
 */
class Version
{
    /** Framework version */
    const VERSION = '6.0';
    /** Revision */
    const REVISION = 2;
    /** Pre-version marker */
    const PREVERSION = null;

    /**
     * Get build
     *
     * @return string
     */
    public static function getBuild()
    {
        static $revision = null;
        if (!is_null($revision)) {
            return $revision;
        }
        return $revision = self::VERSION . '.' . self::REVISION . self::PREVERSION;
    }
}
