<?php

namespace Difra;

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
     * @return Resourcer\CSS|Resourcer\JS|Resourcer\XSLT|Resourcer\Menu|Resourcer\Locale
     * @throws Exception
     */
    public static function getInstance($type, $quiet = false)
    {
        switch ($type) {
            case 'css':
                return Resourcer\CSS::getInstance();
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
