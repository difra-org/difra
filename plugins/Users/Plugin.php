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
    protected $require = ['database','captcha'];
    protected $version = 6;
    protected $description = 'User accounts';

    public function init()
    {
        // Load session instead of events. EVENT_CONFIG_LOAD happens before EVENT_PLUGIN_INIT
        // Events::register(Events::EVENT_CONFIG_LOAD, '\Difra\Plugins\Users\Session', 'load');
        \Difra\Plugins\Users\Session::load();
    }
}
