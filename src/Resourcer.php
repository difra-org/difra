<?php

namespace Difra;

use Difra\Resourcer\CSS;
use Difra\Resourcer\SCSS;

/**
 * Class Resourcer
 * @package Difra
 */
class Resourcer
{
    /**
     * Resourcers factory
     * @param string $type
     * @param bool $quiet
     * @return Resourcer\Abstracts\Common
     * @throws Exception
     */
    public static function getInstance($type, $quiet = false)
    {
        switch ($type) {
            case 'css':
                return Resourcer\Styles::getInstance();
            case 'js':
                return Resourcer\JS::getInstance();
            case 'xslt':
                return Resourcer\XSLT::getInstance();
            case 'menu':
                return Resourcer\Menu::getInstance();
            case 'locale':
                return Resourcer\Locale::getInstance();
            default:
                if (!$quiet) {
                    throw new Exception("Resourcer does not support resource type '$type'");
                }
                return null;
        }
    }
}
