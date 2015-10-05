<?php

namespace Difra\Plugins\Users;

use Difra\Events;

/**
 * Class Plugin
 * @package Difra\Plugins\Users
 */
class Plugin extends \Difra\Plugin
{
    protected $provides = 'auth';
    protected $require = 'database';
    protected $version = 6;
    protected $description = 'User accounts';

    public function init()
    {
        Events::register('config', '\Difra\Plugins\Users\Session', 'load');
        Events::register('config', '\Difra\Plugins\Users', 'bind');
    }
}
