<?php

namespace Difra\Envi;

use Difra\Envi;

/**
 * Class Version
 * @package Difra\Envi
 */
class Version
{
    /** Framework version */
    const VERSION = '6.0';
    /** Version postfix */
    const POSTFIX = 'alpha2';
    /** Revision */
    const REVISION = '$Rev: 1160 $';

    /**
     * Get build
     * @return string
     */
    public static function getBuild()
    {
        static $revision = null;
        if (!is_null($revision)) {
            return $revision;
        }
        // version number
        $revisionArr = [self::VERSION, self::POSTFIX];
        if (Envi::isProduction()) {
            // fw revision
            $fwVer = self::getSVNRev(DIR_FW);
            if ($fwVer !== false) {
                $revisionArr[] = $fwVer;
            } elseif (preg_match('/\d+/', self::REVISION, $match)) {
                $revisionArr[] = $match[0];
            }
            // site revision
            $siteVer = self::getSVNRev(DIR_ROOT);
            if ($siteVer !== false) {
                $revisionArr[] = $siteVer;
            }
        } else {
            $revisionArr[] = time();
        }
        return $revision = implode('.', $revisionArr);
    }

    /**
     * Get revision number from Subversion files
     * @param string $dir Path to search for subversion files
     * @return int|bool
     */
    private static function getSVNRev($dir)
    {
        // try to get svn 1.7 revision
        if (class_exists('\SQLite3') and is_readable($dir . '.svn/wc.db')) {
            try {
                $sqlite = new \SQLite3($dir . '.svn/wc.db');
                $res = $sqlite->query('SELECT MAX(revision) FROM `NODES`');
                $res = $res->fetchArray();
                return $res[0];
            } catch (\Exception $ex) {
            }
        } else { // try to get old svn revision
            if (is_file($dir . '.svn/entries')) {
                $svn = file($dir . '.svn/entries');
            }
            if (isset($svn[3])) {
                return trim($svn[3]);
            }
        }
        return false;
    }
}
