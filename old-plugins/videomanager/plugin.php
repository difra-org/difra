<?php

namespace Difra\Plugins\VideoManager;

use Difra\Events;

class Plugin extends \Difra\Plugin
{
    protected $version = 3.1;
    protected $description = 'Video library management';

    public function init()
    {

        Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\videoManager', 'getHttpPath');
    }
}
