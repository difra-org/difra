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
        Events::register(Events::EVENT_ACTION_REDEFINE, '\Difra\Plugins\CMS', 'run');
        Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\CMS', 'addMenuXML');
        Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\CMS', 'addSnippetsXML');
    }

    /**
     * @return array|bool
     */
    public function getSitemap()
    {
        return CMS::getSitemap();
    }
}
