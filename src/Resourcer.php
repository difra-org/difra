<?php

declare(strict_types=1);

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
     * @return \Difra\Resourcer\JS|\Difra\Resourcer\XSLT|\Difra\Resourcer\Menu|\Difra\Resourcer\Locale|\Difra\Resourcer\Styles|null
     * @throws \Difra\Exception
     */
    public static function getInstance(string $type, bool $quiet = false): Resourcer\JS|Resourcer\XSLT|Resourcer\Menu|Resourcer\Locale|Resourcer\Styles|null
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
