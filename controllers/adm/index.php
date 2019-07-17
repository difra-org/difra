<?php

namespace Controller\Adm;

class Index extends \Difra\Controller\Adm
{
    public function indexAction()
    {
        \Difra\View::redirect('/adm/status');
    }
}
