<?php

namespace Difra\Controller;

use Difra\Controller;
use Difra\View;

class Adm extends Controller
{
    /**
     * Set instance to adm
     */
    public function dispatch()
    {
        View::$instance = 'adm';
    }

}