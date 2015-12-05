<?php

namespace Difra\Plugins\CMS;

use Difra\Events;
use Difra\Plugins\CMS;

/**
 * Class Plugin
 * @package Difra\Plugins\CMS
 */
class Plugin extends \Difra\Plugin
{
    /** @var float */
    protected $version = 6.0;
    /** @var string */
    protected $description = 'Content management system';
    /** @var array */
    protected $require = ['database', 'Editor'];

    public function init()
    {
        Events::register('pre-action', '\Difra\Plugins\CMS', 'run');
        Events::register('dispatch', '\Difra\Plugins\CMS', 'addMenuXML');
        Events::register('dispatch', '\Difra\Plugins\CMS', 'addSnippetsXML');
    }

    /**
     * @return array|bool
     */
    public function getSitemap()
    {
        return CMS::getSitemap();
    }
}
