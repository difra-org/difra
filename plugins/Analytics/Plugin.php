<?php

namespace Difra\Plugins\Analytics;

use Difra\Events;

/**
 * Class Plugin
 * @package Difra\Plugins\Analytics
 */
class Plugin extends \Difra\Plugin
{
    /** @var float */
    protected $version = 6.0;
    /** @var string */
    protected $description = 'Google Analytics code';
    /** @var string[] */
    protected $require = [];

    public function init()
    {
        Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\Analytics', 'addAnalyticsXML');
    }
}
