<?php

namespace Difra\Plugins\Analytics;

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
        \Difra\Events::register('dispatch', '\Difra\Plugins\Analytics', 'addAnalyticsXML');
    }
}
