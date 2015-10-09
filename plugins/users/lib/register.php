<?php

namespace Difra\Plugins\Users;

use Difra\Ajaxer;
use Difra\Exception;
use Difra\Locales;
use Difra\DB;
use Difra\Plugger;
use Difra\Plugins\Users;

/**
 * Class Register
 * @package Difra\Plugins\Users
 */
class Register
{
    // error codes, should match language strings auth/register/*
    const REGISTER_EMAIL_EMPTY = 'email_empty';
    const REGISTER_EMAIL_INVALID = 'email_invalid';
    const REGISTER_EMAIL_EXISTS = 'email_dupe';
    const REGISTER_EMAIL_OK = 'email_ok';
    const REGISTER_PASSWORD1_EMPTY = 'password1_empty';
    const REGISTER_PASSWORD1_OK = 'password1_ok';
    const REGISTER_PASSWORD2_EMPTY = 'password2_empty';
    const REGISTER_PASSWORD2_OK = 'password2_ok';
    const REGISTER_PASSWORD_SHORT = 'password1_short';
    const REGISTER_PASSWORDS_DIFF = 'passwords_diff';
    const REGISTER_CAPCHA_EMPTY = 'capcha_empty';
    const REGISTER_CAPCHA_INVALID = 'capcha_invalid';
    const REGISTER_CAPCHA_OK = 'capcha_ok';
    const REGISTER_LOGIN_EMPTY = 'login_empty';
    const REGISTER_LOGIN_INVALID = 'login_invalid';
    const REGISTER_LOGIN_EXISTS = 'login_dupe';
    const REGISTER_LOGIN_OK = 'login_ok';
    const REGISTER_LOGIN_VALIDATE = '/^[a-zA-Z0-9][a-zA-Z0-9._-]+$/u';
    const MIN_PASSWORD_LENGTH = 6;
    private $failures = [];
    private $successful = [];
    private $email = null;
    private $login = null;
    private $password1 = null;
    private $password2 = null;
    private $capcha = null;
    private $ignoreEmpty = false;
    private $fast = false;
    private $valid = false;

    /**
     * @param bool $ignoreEmpty Report only invalid fields (skip empty or fine fields reporting)
     * @param bool|null $fast true = skip database queries, false = query database, null = depending on capcha
     */
    public function __construct($ignoreEmpty = false, $fast = null)
    {
        $this->ignoreEmpty = $ignoreEmpty;
        $this->fast = $fast;
    }

    /**
     * Set e-mail
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = (string)$email;
        $this->valid = false;
    }

    /**
     * Verify e-mail
     * @param bool|false $fast
     * @return null|string
     */
    private function verifyEmail($fast = false)
    {
        // check e-mail
        if (!$this->ignoreEmpty) {
            if ($this->email === '') {
                return $this->failures['email'] = self::REGISTER_EMAIL_EMPTY;
            } elseif (!self::isEmailValid($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_INVALID;
            } elseif (!$fast and !self::isEmailAvailable($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_EXISTS;
            } else {
                return $this->successful['email'] = self::REGISTER_EMAIL_OK;
            }
        } elseif ($this->email !== '') {
            if (!self::isEmailValid($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_INVALID;
            } elseif (!$fast and !self::isEmailAvailable($this->email)) {
                return $this->failures['email'] = self::REGISTER_EMAIL_EXISTS;
            }
        }
        return null;
    }

    /**
     * Validate e-mail address
     * @param $email
     * @return bool
     */
    private static function isEmailValid($email)
    {
        return (bool)preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,10})$/', $email);
    }

    /**
     * Isn't e-mail exists?
     * @param $email
     * @return bool
     * @throws Exception
     */
    private static function isEmailAvailable($email)
    {
        return DB::getInstance(Users::getDB())->fetchOne(
            'SELECT `id` FROM `user` WHERE `email`=?',
            [$email]
        ) ? false : true;
    }

    /**
     * Set user name
     * @param $login
     * @throws Exception
     */
    public function setLogin($login)
    {
        if (!Users::isLoginNamesEnabled()) {
            throw new Exception('User names are disabled');
        }
        $this->login = $login;
        $this->valid = false;
    }

    /**
     * Verify user name
     * @param bool $fast Skip database checks
     * @return null|string
     */
    public function verifyLogin($fast = false)
    {
        if (!Users::isLoginNamesEnabled()) {
            return null;
        }
        // check e-mail
        if (!$this->ignoreEmpty) {
            if ($this->login === '') {
                return $this->failures['login'] = self::REGISTER_LOGIN_EMPTY;
            } elseif (!self::isLoginValid($this->email)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_INVALID;
            } elseif (!$fast and !self::isLoginAvailable($this->email)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_EXISTS;
            } else {
                return $this->successful['login'] = self::REGISTER_LOGIN_OK;
            }
        } elseif ($this->login !== '') {
            if (!self::isLoginValid($this->login)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_INVALID;
            } elseif (!$fast and !self::isLoginAvailable($this->email)) {
                return $this->failures['login'] = self::REGISTER_LOGIN_EXISTS;
            }
        }
        return null;
    }

    /**
     * Verify if user name string is valid
     * @param $login
     * @return bool
     */
    public static function isLoginValid($login)
    {
        return (bool)preg_match(self::REGISTER_LOGIN_VALIDATE, $login);
    }

    /**
     * Verify if login does not exist yet
     * @param $login
     * @return bool
     * @throws \Difra\Exception
     */
    public static function isLoginAvailable($login)
    {
        return DB::getInstance(Users::getDB())->fetchOne(
            'SELECT `id` FROM `user` WHERE `login`=?',
            [$login]
        ) ? false : true;
    }

    /**
     * Set password
     * @param string $password1
     */
    public function setPassword1($password1)
    {
        $this->password1 = (string)$password1;
        $this->valid = false;
    }

    /**
     * Validate password
     * @return string|null
     */
    private function verifyPassword1()
    {
        if (!$this->ignoreEmpty) {
            if ($this->password1 === '') {
                return $this->failures['password1'] = self::REGISTER_PASSWORD1_EMPTY;
            } elseif (strlen($this->password1) < self::MIN_PASSWORD_LENGTH) {
                return $this->failures['password1'] = self::REGISTER_PASSWORD_SHORT;
            } else {
                return $this->successful['password1'] = self::REGISTER_PASSWORD1_OK;
            }
        } elseif ($this->password1 !== '') {
            if (strlen($this->password1) < self::MIN_PASSWORD_LENGTH) {
                return $this->failures['password1'] = self::REGISTER_PASSWORD_SHORT;
            }
        }
        return null;
    }

    /**
     * Set password (repeat)
     * @param string $password2
     */
    public function setPassword2($password2)
    {
        $this->password2 = (string)$password2;
        $this->valid = false;
    }

    /**
     * Validate password (repeat)
     * @return string
     */
    private function verifyPassword2()
    {
        if (!$this->ignoreEmpty) {
            if ($this->password2 === '') {
                return $this->failures['password2'] = self::REGISTER_PASSWORD2_EMPTY;
            } elseif ($this->password1 !== $this->password2) {
                return $this->failures['password2'] = self::REGISTER_PASSWORDS_DIFF;
            } else {
                return $this->successful['password2'] = self::REGISTER_PASSWORD2_OK;
            }
        } elseif ($this->password2 !== '') {
            if ($this->password1 !== '' and $this->password1 != $this->password2) {
                return $this->failures['password2'] = self::REGISTER_PASSWORDS_DIFF;
            }
        }
        return null;
    }

    /**
     * Set capcha
     * @param string $capcha
     */
    public function setCapcha($capcha)
    {
        $this->capcha = (string)$capcha;
        $this->valid = false;
    }

    /**
     * Validate capcha
     * @return string
     */
    private function verifyCapcha()
    {
        /** @var \Difra\Plugins\Capcha $captcha */
        $captcha = Plugger::getClass('captcha');
        if (!$this->ignoreEmpty) {
            if (!$this->capcha) {
                return $this->failures['capcha'] = self::REGISTER_CAPCHA_EMPTY;
            } elseif (!$captcha::getInstance()->verifyKey($this->capcha)) {
                return $this->failures['capcha'] = self::REGISTER_CAPCHA_INVALID;
            } else {
                return $this->successful['capcha'] = self::REGISTER_CAPCHA_OK;
            }
        } elseif ($this->capcha !== '') {
            if (!$captcha::getInstance()->verifyKey($this->capcha)) {
                return $this->failures['capcha'] = self::REGISTER_CAPCHA_INVALID;
            }
        }
        return null;
    }

    /**
     * Validate registration form fields
     * @return bool
     */
    public function validate()
    {
        $fast = !is_null($this->fast)
            ? $this->fast
            : ($this->verifyCapcha() != self::REGISTER_CAPCHA_OK);
        $this->verifyEmail(!$fast);
        $this->verifyLogin(!$fast);
        $this->verifyPassword1();
        $this->verifyPassword2();

        return $this->valid = empty($this->failures);
    }

    /**
     * Passwords validation
     * @return bool
     */
    public function validatePasswords()
    {
        $this->verifyPassword1();
        $this->verifyPassword2();
        return empty($this->failures);
    }

    /**
     * Add ajaxer events to highlight wrong or correct fields
     * @return bool
     */
    public function callAjaxerEvents()
    {
        if (!empty($this->successful)) {
            foreach ($this->failures as $field => $result) {
                Ajaxer::status($field, Locales::get('auth/register/' . $result), 'ok');
            }
        }
        if (!empty($this->failures)) {
            foreach ($this->failures as $field => $result) {
                Ajaxer::status($field, Locales::get('auth/register/' . $result), 'error');
            }
            return false;
        }
        return true;
    }

    /**
     * Process registration
     * @throws Exception
     */
    public function register()
    {
        if (!$this->valid) {
            $this->validate();
            if (!$this->valid) {
                throw new Exception('Registration aborted: invalid data');
            }
        }
        $user = User::create();
        $user->setEmail($this->email);
        $user->setPassword($this->password1);
        $user->save();
    }

    const ACTIVATE_NOTFOUND = 'activate_notfound';
    const ACTIVATE_USED = 'activate_used';

//    const ACTIVATE_TIMEOUT = 'activate_timeout'; // think about it. warning: no language string for this.

    /**
     * Activate user
     * @param $key
     * @return bool
     * @throws Exception
     */
    public static function activate($key)
    {
        $key = trim((string)$key);
        if (!$key) {
            throw new Exception(self::ACTIVATE_NOTFOUND);
        }
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow('SELECT * FROM `user` WHERE `activation`=? LIMIT 1', [(string)$key]);
        if (empty($data)) {
            throw new Exception(self::ACTIVATE_NOTFOUND);
        }
        if ($data['active']) {
            throw new Exception(self::ACTIVATE_USED);
        }
//        if ($data['registered'] < date('Y-m-d H:i:s', time() - Users::ACTIVATE_TTL)) {
//            throw new Exception(self::ACTIVATE_TIMEOUT);
//        }
        $db->query("UPDATE `user` SET `active`='1',`activation`=NULL WHERE `activation`=?", [$key]);
        return true;
    }
}
