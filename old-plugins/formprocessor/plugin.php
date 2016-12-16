<?php

namespace Difra\Plugins\FormProcessor;

use Difra\Events;

class Plugin extends \Difra\Plugin
{
    protected $version = 3.1;
    protected $description = 'Custom feedback forms';

    public function init()
    {

        \Difra\Events::register(Events::EVENT_ACTION_PRE_RUN, '\Difra\Plugins\FormProcessor', 'run');
    }
}
