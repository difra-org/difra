<?php

use Difra\Ajaxer;
use Difra\Plugins\Users, Difra\Param;
use Difra\View;

class RegisterController extends Difra\Controller
{
    /**
     * Registration form (page)
     */
    public function indexAction()
    {
        $this->root->appendChild($this->xml->createElement('register'));
    }

    /**
     * Authorized user (error)
     */
    public function indexActionAuth()
    {
        // TODO: already registered
        // TODO: log
        View::redirect('/');
    }

    /**
     * Registration form (ajax)
     */
    public function indexAjaxAction()
    {
        $this->root->appendChild($this->xml->createElement('register'));
        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Authorized user (error)
     */
    public function indexAjaxActionAuth()
    {
        // TODO: message
        Ajaxer::reload();
        // TODO: log
    }

    /**
     * Registration form submit (registration page version)
     * @param Param\AjaxCheckbox $accept
     * @param Param\AjaxString|null $email
     * @param Param\AjaxString|null $password1
     * @param Param\AjaxString|null $password2
     * @param Param\AjaxString|null $login
     * @param Param\AjaxString|null $capcha
     * @throws \Difra\Exception
     */
    public function submitAjaxAction(
        Param\AjaxCheckbox $accept,
        Param\AjaxString $email = null,
        Param\AjaxString $password1 = null,
        Param\AjaxString $password2 = null,
        Param\AjaxString $login = null,
        Param\AjaxString $capcha = null
    )
    {
        $ok = true;

        $register = new Users\Register();
        $register->setEmail($email);
        $register->setPassword1($password1);
        $register->setPassword2($password2);
        $register->setCapcha($capcha);

        /*        $addit = \Difra\Additionals::getStatus('users', Ajaxer::parameters);
                if (is_array($addit) and !empty($addit)) {
                    foreach ($addit as $name => $status) {
                        switch ($status) {
                            case \Difra\Additionals::FIELD_OK:
                                Ajaxer::status($name, Locales::get('additionals/users/' . $name . '/ok'), 'ok');
                                break;
                            case \Difra\Additionals::FIELD_EMPTY:
                                Ajaxer::status(
                                    $name,
                                    Locales::get('additionals/users/' . $name . '/empty'), 'error'
                                );
                                $ok = false;
                                break;
                            case \Difra\Additionals::FIELD_DUPE:
                                Ajaxer::status(
                                    $name,
                                    Locales::get('additionals/users/' . $name . '/dupe'), 'error'
                                );
                                $ok = false;
                                break;
                            case \Difra\Additionals::FIELD_BAD:
                                Ajaxer::status(
                                    $name, Locales::get('additionals/users/' . $name . '/bad_symbols'), 'error'
                                );

                                $ok = false;
                                break;
                        }
                    }
                }*/
        if (!$register->validate()) {
            $register->callAjaxerEvents();
            return;
        }

        // EULA
        // TODO: if EULA is enabled, show it
        if (!$accept->val() and \Difra\Config::getInstance()->getValue('auth', 'eula')) {
            $this->root->appendChild($this->xml->createElement('eula'));
            Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
            return;
        }

        // TODO: register user
//        $register->run();

        // TODO: do something
//        $users = Users::getInstance();
//        $res = $users->register(Ajaxer::parameters);
//        if ($res === true) {
//            Ajaxer::notify(
//                Locales::get('auth/register/complete-' . Users::getInstance()->getActivationMethod())
//            );
//            Ajaxer::close();
//        } else {
//            Ajaxer::error('Unknown error: ' . $res);
//        }
    }

    /**
     * Authorized user (error)
     */
    public function submitAjaxActionAuth()
    {
        // TODO: handle registered user
        // TODO: log
    }

//    /**
//     * E-mail activation
//     * @param \Difra\Param\AnyString $code
//     * @return void
//     */
//    public function activateAction(Param\AnyString $code)
//    {
//        $res = Users::activate($code);
//        if ($res === true) {
//            \Difra\Libs\Cookies::getInstance()->notify(Locales::get('auth/activate/done'));
//        } else {
//            \Difra\Libs\Cookies::getInstance()->notify(
//                Locales::get('auth/activate/' . $res), true
//            );
//        }
//        \Difra\View::redirect('/');
//    }

//    /**
//     * Change password
//     * @param Difra\Param\AjaxString $oldpassword
//     * @param Difra\Param\AjaxString $password1
//     * @param Difra\Param\AjaxString $password2
//     * @return void
//     */
//    public function changepasswordAjaxActionAuth(
//        Param\AjaxString $oldpassword,
//        Param\AjaxString $password1,
//        Param\AjaxString $password2
//    )
//    {
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

