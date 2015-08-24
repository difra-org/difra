<?php

namespace Difra\Libs;

use Difra\Debugger;

include_once(DIR_FW . 'lib/libs/less/lessc.inc.php');

/**
 * Class Less
 * LESS support.
 *
 * @package Difra\Libs
 */
class Less
{
    /**
     * Convert LESS to CSS
     *
     * @param string $string
     * @return string
     */
    public static function compile($string)
    {
        static $less = null;
        if (!$less) {
            $less = new \lessc;
            if (!Debugger::isEnabled()) {
                $less->setFormatter('compressed');
                $less->setPreserveComments(false);
            } else {
                $less->setFormatter('lessjs');
                $less->setPreserveComments(true);
            }
        }

        return $less->compile($string);
    }
}