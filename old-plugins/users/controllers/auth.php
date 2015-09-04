<?php

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Plugins\Users, Difra\Param;
use Difra\View;

class AuthController extends Difra\Controller
{
    /**
     * Форма логина
     * @return void
     */
    public function authorizationAjaxAction()
    {
        if (\Difra\Auth::getInstance()->logged) {
            Ajaxer::reload();
            return;
        }
        $this->root->appendChild($this->xml->createElement('login'));
        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Логин в систему
     * @param Difra\Param\AjaxString $email
     * @param Difra\Param\AjaxString $password
     * @param Difra\Param\AjaxCheckbox $rememberMe
     * @return void
     */
    public function loginAjaxAction(Param\AjaxString $email, Param\AjaxString $password, Param\AjaxCheckbox $rememberMe)
    {
        $auth = Difra\Auth::getInstance();
        if ($auth->logged) {
            Ajaxer::reload();
            return;
        }
        $users = Users::getInstance();
        $res = $users->login($email->val(), $password->val(), ($rememberMe->val() == 1) ? true : false);
        if ($res === true) {
            Ajaxer::reload();
            return;
        }
        switch ($res) {
            case Users::LOGIN_BADPASSWORD:
                Ajaxer::status('password', Locales::get('auth/login/' . $res), 'problem');
                break;
            default:
                Ajaxer::status('email', Locales::get('auth/login/' . $res), 'problem');
        }
    }

    /**
     * Выход из системы
     * @return void
     */
    public function logoutAjaxAction()
    {
        $Auth = Difra\Auth::getInstance();
        $id = $Auth->getId();
        $Auth->logout();

        // в случае ручного логаута убираем длинную сессию
        \Difra\Plugins\Users::getInstance()->unSetLongSession($id);

        // TODO: сделать так, чтобы в случаях, если страница требует авторизации, происходил редирект на главную
        Ajaxer::reload();
    }

    /**
     * Форма восстановления пароля
     * @return void
     */
    public function recoveryAjaxAction()
    {
        if (\Difra\Auth::getInstance()->logged) {
            Ajaxer::reload();
            return;
        }
        $this->root->appendChild($this->xml->createElement('recover'));
        Ajaxer::display(View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Восстановление пароля
     * @param \Difra\Param\AjaxString $email
     * @return void
     */
    public function recoverAjaxAction(Param\AjaxString $email)
    {

        $auth = Difra\Auth::getInstance();
        if ($auth->logged) {
            Ajaxer::reload();
            return;
        }
        $users = Users::getInstance();
        $res = $users->recover($email->val());
        Ajaxer::setResponse('error', $res);
        if ($res !== true) {
            Ajaxer::status('email', Locales::get('auth/login/' . $res), 'problem');
            return;
        }
        Ajaxer::notify(Locales::get('auth/login/recovered'));
        Ajaxer::close();
    }

    /**
     * Ссылки из писем для восстановления пароля
     * @param Difra\Param\AnyString $code
     * @return void
     */
    public function recoverAction(Param\AnyString $code)
    {

        $code = trim($code->val());
        if (ctype_alnum($code)) {
            \Difra\Libs\Cookies::getInstance()->query('/auth/recover2/' . $code);
        } else {
            \Difra\Libs\Cookies::getInstance()->notify(
                Locales::get('auth/recover/bad_link')
            );
        }
        \Difra\View::redirect('/');
    }

    /**
     * Форма установки нового пароля
     * @param Difra\Param\AnyString $code
     * @return void
     */
    public function recover2AjaxAction(Param\AnyString $code)
    {
        if (Difra\Auth::getInstance()->logged) {
            Ajaxer::error(Locales::get('auth/recover/already_logged'));
            return;
        }
        $res = Users::getInstance()->verifyRecover($code->val());
        if ($res !== true) {
            Ajaxer::error(Locales::get('auth/recover/' . $res));
            return;
        }
        /** @var \DOMElement $recoverNode */
        $recoverNode = $this->root->appendChild($this->xml->createElement('recover2'));
        $recoverNode->setAttribute('code', $code->val());

        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Сохранение нового пароля
     * @param Difra\Param\AnyString $code
     * @param Difra\Param\AjaxString $password1
     * @param Difra\Param\AjaxString $password2
     * @return void
     */
    public function recover3AjaxAction(Param\AnyString $code, Param\AjaxString $password1, Param\AjaxString $password2)
    {
        if (Difra\Auth::getInstance()->logged) {
            Ajaxer::error(Locales::get('auth/recover/already_logged'));
            return;
        }
        $res = Users::getInstance()->verifyRecover($code->val());
        if ($res !== true) {
            Ajaxer::error(Locales::get('auth/recover/' . $res));
            return;
        }
        $error =
            \Difra\Plugins\Users::getInstance()->recoverSetPassword($code->val(), $password1->val(), $password2->val());
        if ($error !== true) {
            echo $error;
            Ajaxer::status('password1', Locales::get('auth/recover/' . $error), 'problem');
            return;
        }
        Ajaxer::notify(Locales::get('auth/recover/done'));
        Ajaxer::close();
    }

    /**
     * Форма регистрации
     * @return void
     */
    public function registerAjaxAction()
    {
        if (\Difra\Auth::getInstance()->logged) {
            Ajaxer::reload();
            return;
        }
        $this->root->appendChild($this->xml->createElement('register'));
        Ajaxer::display(\Difra\View::render($this->xml, 'auth-ajax', true));
    }

    /**
     * Проверка формы регистрации и сама регистрация
     * @param Difra\Param\AjaxCheckbox $accept
     * @param Difra\Param\AjaxString|null $email
     * @param Difra\Param\AjaxString|null $password1
     * @param Difra\Param\AjaxString|null $password2
     * @param Difra\Param\AjaxString|null $capcha
     * @return void
     */
    public function register2AjaxAction(
        Param\AjaxCheckbox $accept, Param\AjaxString $email = null, Param\AjaxString $password1 = null,
        Param\AjaxString $password2 = null, Param\AjaxString $capcha = null
    ) {
        $auth = Difra\Auth::getInstance();
        if ($auth->logged) {
            Ajaxer::error(Locales::get('auth/register/already_logged'));
            return;
        }
        $users = Users::getInstance();
        $ok = true;

        if (!$email or !$email->val()) {
            Ajaxer::status('email', Locales::get('auth/register/email_empty'), 'error');
            $ok = false;
        } elseif (!$users->isEmailValid($email->val())) {
            Ajaxer::status('email', Locales::get('auth/register/email_invalid'), 'error');
            $ok = false;
        } elseif ($users->checkLogin($email->val())) {
            Ajaxer::status('email', Locales::get('auth/register/email_dupe'), 'error');
            $ok = false;
        } else {
            Ajaxer::status('email', Locales::get('auth/register/email_ok'), 'ok');
        }
        if (!$password1 or !$password1->val()) {
            Ajaxer::status('password1', Locales::get('auth/register/password1_empty'), 'error');
            $ok = false;
        } elseif (strlen($password1->val()) < 6) {
            Ajaxer::status('password1', Locales::get('auth/register/password1_short'), 'error');
            $ok = false;
        } else {
            Ajaxer::status('password1', Locales::get('auth/register/password1_ok'), 'ok');
        }
        if (!$password2 or !$password2->val()) {
            Ajaxer::status('password2', Locales::get('auth/register/password2_empty'), 'error');
            $ok = false;
        } elseif ($password1->val() != $password2->val()) {
            Ajaxer::status('password2', Locales::get('auth/register/passwords_diff'), 'error');
            $ok = false;
        } else {
            Ajaxer::status('password2', Locales::get('auth/register/password2_ok'), 'ok');
        }
        if (!$capcha or !$capcha->val()) {
            Ajaxer::status('capcha', Locales::get('auth/register/capcha_empty'), 'error');
            $ok = false;
        } elseif (!\Difra\Libs\Capcha::getInstance()->verifyKey($capcha->val())) {
            Ajaxer::status('capcha', Locales::get('auth/register/capcha_invalid'), 'error');
        } else {
            Ajaxer::status('capcha', Locales::get('auth/register/capcha_ok'), 'ok');
        }

        $addit = \Difra\Additionals::getStatus('users', Ajaxer::parameters);
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
        }
        if (!$ok) {
            return;
        }

        // TODO: опцию для включения показа EULA
        /*
        if( !$accept->val() ) {
            $this->root->appendChild( $this->xml->createElement( 'eula' ) );
            $this->ajax->display( $this->view->render( $this->xml, 'auth-ajax', true ) );
            return;
        }
        */

        $users = Users::getInstance();
        $res = $users->register(Ajaxer::parameters);
        if ($res === true) {
            Ajaxer::notify(
                Locales::get('auth/register/complete-' . Users::getInstance()->getActivationMethod())
            );
            Ajaxer::close();
        } else {
            Ajaxer::error('Unknown error: ' . $res);
        }
    }

    /**
     * Активация учётных записей (по ссылке из e-mail)
     * @param \Difra\Param\AnyString $code
     * @return void
     */
    public function activateAction(Param\AnyString $code)
    {

        $res = Users::getInstance()->activate($code);
        if ($res === true) {
            \Difra\Libs\Cookies::getInstance()->notify(Locales::get('auth/activate/done'));
        } else {
            \Difra\Libs\Cookies::getInstance()->notify(
                Locales::get('auth/activate/' . $res), true
            );
        }
        \Difra\View::redirect('/');
    }

    /**
     * Смена пароля с указанием старого
     * @param Difra\Param\AjaxString $oldpassword
     * @param Difra\Param\AjaxString $password1
     * @param Difra\Param\AjaxString $password2
     * @return void
     */
    public function changepasswordAjaxActionAuth(
        Param\AjaxString $oldpassword, Param\AjaxString $password1, Param\AjaxString $password2
    ) {

        $ok = true;
        if (!Users::getInstance()->verifyPassword($oldpassword)) {
            Ajaxer::status('oldpassword', Locales::get('auth/password/bad_old'), 'problem');
            $ok = false;
        }
        if (!$password1 or !$password1->val()) {
            Ajaxer::status('password1', Locales::get('auth/register/password1_empty'), 'problem');
            $ok = false;
        } elseif (strlen($password1->val()) < 6) {
            Ajaxer::status('password1', Locales::get('auth/register/password1_short'), 'problem');
            $ok = false;
        }
        if (!$password2 or !$password2->val()) {
            Ajaxer::status('password2', Locales::get('auth/register/password2_empty'), 'problem');
            $ok = false;
        } elseif ($password1->val() != $password2->val()) {
            Ajaxer::status('password2', Locales::get('auth/register/passwords_diff'), 'problem');
            $ok = false;
        }
        if ($ok) {
            Ajaxer::notify(Locales::get('auth/password/changed'));
            Ajaxer::reset();
        }
    }
}

