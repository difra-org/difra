<?php

namespace Difra\Controller;

use Difra\Controller;
use Difra\Envi;
use Difra\View;

class Adm extends Controller
{
    /**
     * Set instance to adm
     */
    public function dispatch()
    {
        View::$instance = 'adm';
        if (!$this->root->getAttribute('title')) {
            $this->root->setAttribute('title', Envi::getHost() . '/adm');
        }
    }

}
