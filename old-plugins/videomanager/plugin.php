<?php

namespace Difra\Plugins\VideoManager;

class Plugin extends \Difra\Plugin
{
    protected $version = 3.1;
    protected $description = 'Video library management';

    public function init()
    {

        \Difra\Events::register('dispatch', '\Difra\Plugins\videoManager', 'getHttpPath');
    }
}
