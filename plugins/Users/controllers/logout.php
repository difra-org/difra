<?php

use Difra\Ajaxer;
use Difra\Plugins\Users\User;

/**
 * Class LogoutController
 */
class LogoutController extends \Difra\Controller
{
    /**
     * Log out
     */
    public function indexAction()
    {
        User::logout();
        \Difra\View::redirect('/');
    }

    /**
     * Log out (ajax)
     */
    public function indexAjaxAction()
    {
        User::logout();
        // TODO: redirect to / if page requires auth
        Ajaxer::reload();
    }
}
