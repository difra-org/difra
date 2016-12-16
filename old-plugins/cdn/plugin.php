<?php

namespace Difra\Plugins\CDN;

use Difra\Events;

class Plugin extends \Difra\Plugin
{
    protected $version = 3.1;
    protected $description = 'Content delivery network';
    protected $require = 'mysql';

    public function init()
    {

        \Difra\Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\CDN', 'selectHost');
    }
}
