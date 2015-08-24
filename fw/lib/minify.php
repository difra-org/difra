<?php

namespace Difra;

/**
 * Class Minify
 *
 * @package Difra
 */
class Minify
{
    /**
     * @param string $type
     * @return \Difra\Minify\Common
     */
    static public function getInstance($type)
    {
        switch ($type) {
            case 'css':
                return Minify\CSS::getInstance();
            case 'js':
                return Minify\JS::getInstance();
            default:
                return Minify\None::getInstance();
        }
    }
}
