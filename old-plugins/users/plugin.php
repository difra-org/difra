<?php

namespace Difra\Plugins\Users;

use Difra\Events;

class Plugin extends \Difra\Plugin
{
    protected $provides = 'auth';
    protected $require = 'mysql';
    protected $version = '4';
    protected $description = 'User accounts';

    public function init()
    {

        Events::register('config', '\Difra\Plugins\Users', 'checkLongSession');
    }
}
