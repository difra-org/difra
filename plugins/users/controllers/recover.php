<?php

use Difra\Ajaxer;
use Difra\Exception;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Param\AjaxString;
use Difra\Param\AnyString;
use Difra\Plugins\Users\Recover;
use Difra\View;

class RecoverController extends \Difra\Controller
{
//    /**
//     * Recover password page
//     */
//    public function indexAction()
//    {
//        // TODO
//    }

    /**
     * Recover password (ajax)
     * @param AjaxString $login Login or e-mail
     * @throws Exception
     */
    public function indexAjaxAction(AjaxString $login = null)
    {
        // show recover form
        if (is_null($login)) {
            $this->root->appendChild($this->xml->createElement('recover'));
            Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
            return;
        }
        // login's empty
        if ($login->val() === '') {
            Ajaxer::required('login');
            return;
        }
        // recover
        try {
            Recover::send($login->val());
            Ajaxer::close();
            Ajaxer::notify(Locales::get('auth/login/recovered'));
        } catch (Exception $ex) {
            Ajaxer::status('email', Locales::get('auth/login/' . $ex->getMessage()), 'problem');
        }
    }

    /**
     * Recover password (stub)
     */
    public function indexActionAuth()
    {
        View::redirect('/');
    }

    /**
     * Recover password (ajax, stub)
     */
    public function indexAjaxActionAuth()
    {
        Ajaxer::reload();
    }

    /**
     * Password recovery link
     * @param AnyString $code
     */
    public function codeAction(AnyString $code)
    {
        try {
            Recover::verify($code->val());
        } catch (Exception $ex) {
            Cookies::getInstance()->notify(Locales::get('auth/recover/' . $ex->getMessage()), true);
            View::redirect('/');
        }
        /** @var \DOMElement $recoverNode */
        $recoverNode = $this->root->appendChild($this->xml->createElement('recover2'));
        $recoverNode->setAttribute('code', $code->val());

        Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Password recovery link (stub)
     * @param AnyString $code
     */
    public function codeActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        AnyString $code
    ) {
        Cookies::getInstance()->notify(Locales::get('auth/recover/already_logged'), true);
        View::redirect('/');
    }

    public function submitAjaxAction(AnyString $code, AjaxString $password1, AjaxString $password2)
    {
        try {
            $user = Recover::verify($code->val(), true);
        } catch (Exception $ex) {
            Ajaxer::notify(Locales::get('auth/recover/' . $ex->getMessage()));
            return;
        }
        // verify passwords
        $register = new \Difra\Plugins\Users\Register();
        $register->setPassword1($password1->val());
        $register->setPassword2($password2->val());
        if (!$register->validatePasswords()) {
            $register->callAjaxerEvents();
            return;
        }
        $user->setPassword($password1->val());
        Recover::setUsed($code->val());
        Ajaxer::notify(Locales::get('auth/recover/done'));
    }

    public function submitAjaxActionAuth(
        /** @noinspection PhpUnusedParameterInspection */
        AnyString $code
    ) {
        Ajaxer::error(Locales::get('auth/recover/already_logged'));
    }

//
//    /**
//     * Сохранение нового пароля
//     * @param Difra\Param\AnyString $code
//     * @param Difra\Param\AjaxString $password1
//     * @param Difra\Param\AjaxString $password2
//     * @return void
//     */
//    public function recover3AjaxAction(Param\AnyString $code, Param\AjaxString $password1, Param\AjaxString $password2)
//    {
//        if (Difra\Auth::getInstance()->logged) {
//            Ajaxer::error(Locales::get('auth/recover/already_logged'));
//            return;
//        }
//        $res = Users::getInstance()->verifyRecover($code->val());
//        if ($res !== true) {
//            Ajaxer::error(Locales::get('auth/recover/' . $res));
//            return;
//        }
//        $error =
//            \Difra\Plugins\Users::getInstance()->recoverSetPassword($code->val(), $password1->val(), $password2->val());
//        if ($error !== true) {
//            echo $error;
//            Ajaxer::status('password1', Locales::get('auth/recover/' . $error), 'problem');
//            return;
//        }
//        Ajaxer::notify(Locales::get('auth/recover/done'));
//        Ajaxer::close();
//    }
}
