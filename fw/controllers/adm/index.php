<?php

/**
 * Class AdmIndexController
 * Administrator interface index page.
 * Redirects to stats now.
 */
class AdmIndexController extends Difra\Controller
{
    public function dispatch()
    {
        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {
        if ($this->hasUnusedParameters()) {
            throw new \Difra\View\HttpError(404);
        }
        \Difra\View::redirect('/adm/status');
    }
}
