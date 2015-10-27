<?php

use Difra\Ajaxer;
use Difra\Exception;
use Difra\Libs\Cookies;
use Difra\Locales;
use Difra\Param\AjaxCheckbox;
use Difra\Param\AjaxString;
use Difra\Param\AnyString;
use Difra\Plugins\Users, Difra\Param;
use Difra\Plugins\Users\Register;
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
     * Authorized user (already registered)
     */
    public function indexActionAuth()
    {
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
     * @param AjaxCheckbox $accept
     * @param AjaxCheckbox $ajaxRequest
     * @param AjaxString|null $email
     * @param AjaxString|null $password1
     * @param AjaxString|null $password2
     * @param AjaxString|null $login
     * @param AjaxString|null $capcha
     * @throws Exception
     */
    public function submitAjaxAction(
        AjaxCheckbox $accept,
        AjaxCheckbox $redirect,
        AjaxString $email = null,
        AjaxString $password1 = null,
        AjaxString $password2 = null,
        AjaxString $login = null,
        AjaxString $capcha = null
    ) {
        $register = new Users\Register();
        $register->setEmail($email);
        $register->setLogin($login);
        $register->setPassword1($password1);
        $register->setPassword2($password2);
        $register->setCapcha($capcha);

        if (!$register->validate()) {
            $register->callAjaxerEvents();
            return;
        }

        // EULA
        if (!$accept->val() and \Difra\Config::getInstance()->getValue('auth', 'eula')) {
            $this->root->appendChild($this->xml->createElement('eula'));
            Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
            return;
        }

        $register->register();

        if ($redirect) {
        Cookies::getInstance()->notify(
            Locales::get('auth/register/complete-' . Users::getActivationMethod())
        );
            View::redirect('/');
        } else {
            Ajaxer::notify(
                Locales::get('auth/register/complete-' . Users::getActivationMethod())
            );
            Ajaxer::reload();
        }
    }

    /**
     * Authorized user (error)
     */
    public function submitAjaxActionAuth()
    {
        // TODO: handle registered user
        // TODO: log
    }

    /**
     * Activation link
     * @param AnyString $code
     */
    public function activateAction(AnyString $code)
    {
        try {
            Register::activate($code->val());
            Cookies::getInstance()->notify(Locales::get('auth/activate/done'));
        } catch (\Difra\Exception $error) {
            Cookies::getInstance()->notify(
                Locales::get('auth/activate/' . $error->getMessage()),
                true
            );
        }
        \Difra\View::redirect('/');
    }


//    /**
//     * Old registration form
//     * @return void
//     */
//    public function registerAjaxAction()
//    {
//        if (\Difra\Auth::getInstance()->logged) {
//            Ajaxer::reload();
//            return;
//        }
//        $this->root->appendChild($this->xml->createElement('register'));
//        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
//    }
//
//    /**
//     * Old registration submit form
//     * @param Difra\Param\AjaxCheckbox $accept
//     * @param Difra\Param\AjaxString|null $email
//     * @param Difra\Param\AjaxString|null $password1
//     * @param Difra\Param\AjaxString|null $password2
//     * @param Difra\Param\AjaxString|null $capcha
//     * @return void
//     */
//    public function register2AjaxAction(
//        Param\AjaxCheckbox $accept,
//        Param\AjaxString $email = null,
//        Param\AjaxString $password1 = null,
//        Param\AjaxString $password2 = null,
//        Param\AjaxString $capcha = null
//    ) {
//        $auth = Difra\Auth::getInstance();
//        if ($auth->logged) {
//            Ajaxer::error(Locales::get('auth/register/already_logged'));
//            return;
//        }
//        $users = Users::getInstance();
//        $ok = true;
//
//        if (!$email or !$email->val()) {
//            Ajaxer::status('email', Locales::get('auth/register/email_empty'), 'error');
//            $ok = false;
//        } elseif (!$users->isEmailValid($email->val())) {
//            Ajaxer::status('email', Locales::get('auth/register/email_invalid'), 'error');
//            $ok = false;
//        } elseif ($users->checkLogin($email->val())) {
//            Ajaxer::status('email', Locales::get('auth/register/email_dupe'), 'error');
//            $ok = false;
//        } else {
//            Ajaxer::status('email', Locales::get('auth/register/email_ok'), 'ok');
//        }
//        if (!$password1 or !$password1->val()) {
//            Ajaxer::status('password1', Locales::get('auth/register/password1_empty'), 'error');
//            $ok = false;
//        } elseif (strlen($password1->val()) < 6) {
//            Ajaxer::status('password1', Locales::get('auth/register/password1_short'), 'error');
//            $ok = false;
//        } else {
//            Ajaxer::status('password1', Locales::get('auth/register/password1_ok'), 'ok');
//        }
//        if (!$password2 or !$password2->val()) {
//            Ajaxer::status('password2', Locales::get('auth/register/password2_empty'), 'error');
//            $ok = false;
//        } elseif ($password1->val() != $password2->val()) {
//            Ajaxer::status('password2', Locales::get('auth/register/passwords_diff'), 'error');
//            $ok = false;
//        } else {
//            Ajaxer::status('password2', Locales::get('auth/register/password2_ok'), 'ok');
//        }
//        if (!$capcha or !$capcha->val()) {
//            Ajaxer::status('capcha', Locales::get('auth/register/capcha_empty'), 'error');
//            $ok = false;
//        } elseif (!\Difra\Libs\Capcha::getInstance()->verifyKey($capcha->val())) {
//            Ajaxer::status('capcha', Locales::get('auth/register/capcha_invalid'), 'error');
//        } else {
//            Ajaxer::status('capcha', Locales::get('auth/register/capcha_ok'), 'ok');
//        }
//
//        $addit = \Difra\Additionals::getStatus('users', Ajaxer::parameters);
//        if (is_array($addit) and !empty($addit)) {
//            foreach ($addit as $name => $status) {
//                switch ($status) {
//                    case \Difra\Additionals::FIELD_OK:
//                        Ajaxer::status($name, Locales::get('additionals/users/' . $name . '/ok'), 'ok');
//                        break;
//                    case \Difra\Additionals::FIELD_EMPTY:
//                        Ajaxer::status(
//                            $name,
//                            Locales::get('additionals/users/' . $name . '/empty'), 'error'
//                        );
//                        $ok = false;
//                        break;
//                    case \Difra\Additionals::FIELD_DUPE:
//                        Ajaxer::status(
//                            $name,
//                            Locales::get('additionals/users/' . $name . '/dupe'), 'error'
//                        );
//                        $ok = false;
//                        break;
//                    case \Difra\Additionals::FIELD_BAD:
//                        Ajaxer::status(
//                            $name, Locales::get('additionals/users/' . $name . '/bad_symbols'), 'error'
//                        );
//
//                        $ok = false;
//                        break;
//                }
//            }
//        }
//        if (!$ok) {
//            return;
//        }
//
//        /*
//        if( !$accept->val() ) {
//            $this->root->appendChild( $this->xml->createElement( 'eula' ) );
//            $this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
//            return;
//        }
//        */
//
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

