<?php

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Plugins\Users, Difra\Param;
use Difra\Plugins\Users\User;
use Difra\View;

/**
 * Class LoginController
 */
class LoginController extends Difra\Controller
{
//    /**
//     * Форма логина
//     * @return void
//     */
//    public function indexAction()
//    {
//        if (\Difra\Auth::getInstance()->isAuthorized()) {
//            Ajaxer::reload();
//            return;
//        }
//        $this->root->appendChild($this->xml->createElement('login'));
////        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
//    }

    /**
     * User login
     * @param Difra\Param\AjaxString $login
     * @param Difra\Param\AjaxString $password
     * @param Difra\Param\AjaxCheckbox $rememberMe
     */
    public function indexAjaxAction(Param\AjaxString $login, Param\AjaxString $password, Param\AjaxCheckbox $rememberMe)
    {
        try {
            User::loginByPassword($login->val(), $password->val(), ($rememberMe->val() == 1) ? true : false);
            Ajaxer::reload();
        } catch (\Difra\Exception $ex) {
            switch ($error = $ex->getMessage()) {
                case User::LOGIN_BADPASS:
                    Ajaxer::status('password', Locales::get('auth/login/' . $error), 'problem');
                    break;
                default:
                    Ajaxer::status('login', Locales::get('auth/login/' . $error), 'problem');
            }
        }
    }

    /**
     * User login (stub for logged in users)
     * @param Difra\Param\AjaxString $login
     * @param Difra\Param\AjaxString $password
     * @param Difra\Param\AjaxCheckbox $rememberMe
     */
    public function indexAjaxActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        Param\AjaxString $login,
        Param\AjaxString $password,
        Param\AjaxCheckbox $rememberMe
    ) {
        Ajaxer::reload();
    }

//    /**
//     * Смена пароля с указанием старого
//     * @param Difra\Param\AjaxString $oldpassword
//     * @param Difra\Param\AjaxString $password1
//     * @param Difra\Param\AjaxString $password2
//     * @return void
//     */
//    public function changepasswordAjaxActionAuth(
//        Param\AjaxString $oldpassword,
//        Param\AjaxString $password1,
//        Param\AjaxString $password2
//    ) {
//
//        $ok = true;
//        if (!Users::getInstance()->verifyPassword($oldpassword)) {
//            Ajaxer::status('oldpassword', Locales::get('auth/password/bad_old'), 'problem');
//            $ok = false;
//        }
//        if (!$password1 or !$password1->val()) {
//            Ajaxer::status('password1', Locales::get('auth/register/password1_empty'), 'problem');
//            $ok = false;
//        } elseif (strlen($password1->val()) < 6) {
//            Ajaxer::status('password1', Locales::get('auth/register/password1_short'), 'problem');
//            $ok = false;
//        }
//        if (!$password2 or !$password2->val()) {
//            Ajaxer::status('password2', Locales::get('auth/register/password2_empty'), 'problem');
//            $ok = false;
//        } elseif ($password1->val() != $password2->val()) {
//            Ajaxer::status('password2', Locales::get('auth/register/passwords_diff'), 'problem');
//            $ok = false;
//        }
//        if ($ok) {
//            Ajaxer::notify(Locales::get('auth/password/changed'));
//            Ajaxer::reset();
//        }
//    }
}

