<?php

namespace Difra\Plugins;

use Difra\Config;

/**
 * Class Users
 * @package Difra\Plugins
 */
class Users
{
    const DB = 'users';
    const RECOVER_TTL = 72; // hours
    const ACTIVATE_TTL = 7 * 24; // hours
    const IP_MASK = '255.255.0.0'; // "long session" ip mask

    /**
     * Get database name for users plugin
     * @return string
     */
    static public function getDB()
    {
        return self::DB;
    }

    /**
     * Are user names enabled?
     * @return bool
     */
    static public function isLoginNamesEnabled()
    {
        return (bool)Config::getInstance()->getValue('auth', 'logins');
    }

    /**
     * Get activation method (email, moderate or none)
     * @return string
     */
    public static function getActivationMethod()
    {
        return Config::getInstance()->getValue('auth', 'confirmation') ?: 'email';
    }

    public static function getRecoverTTL()
    {
        return self::RECOVER_TTL;
    }

//    private function _registrationMail($data, $confirm = 'none')
//    {
//        $data2 = [
//            'user' => $data['email'],
//            'confirm' => $confirm,
//            'ttl' => self::ACTIVATE_TTL,
//            'password' => $data['password1'],
//            'code' => $data['activation']
//        ];
//        Mailer::getInstance()->CreateMail($data['email'], 'mail_registration', $data2);
//    }
//
//
//
//    const PW_EMPTY = 'pw_empty';
//    const PW_SHORT = 'pw_short';
//    const PW_DIFF = 'pw_diff';
//
//    /**
//     * Смена пароля текущего пользователя
//     * Возвращает строку с кодом ошибки или true в случае успеха
//     * @param string $oldPass
//     * @param string $newPass
//     * @return mixed
//     */
//    const PW_BADOLD = 'pw_badold';
//
//    public function changePassword($oldPass, $newPass)
//    {
//        $auth = Auth::getInstance();
//        if (!$auth->logged) {
//            throw new Exception("Can't changePassword() for unauthorized user");
//        }
//        if (!$this->verifyPassword($oldPass)) {
//            return static::PW_BADOLD;
//        }
//        return $this->setPassword($auth->getId(), $newPass, $newPass);
//    }
//
//    public function setUserLogin($id, $data)
//    {
//        $mysql = MySQL::getInstance();
//        if (empty($data['email']) or !trim($data['email'])) {
//            return false;
//        }
//        $email = $mysql->escape(trim($data['email']));
//        $passwd = false;
//        if (!empty($data['change_pw']) and $data['change_pw']) {
//            if (!empty($data['new_pw']) and trim($data['new_pw'])) {
//                $passwd = trim($data['new_pw']);
//            }
//        }
//        $mysql->query(
//            "UPDATE `user` SET `email`='$email'" . ($passwd ? ",`password`='" . sha1($passwd) . "'" : '') .
//            ' WHERE `id`='
//            . $mysql->escape($id)
//        );
//
//        if (isset($data['addonFields']) && isset($data['addonValues'])
//            && !is_null($data['addonFields']) && !is_null($data['addonValues'])
//        ) {
//
//            // сохранение дополнительных полей пользоватея
//
//            $query[] = "DELETE FROM `user_field` WHERE `user`='" . intval($id) . "'";
//            $values = [];
//            foreach ($data['addonFields'] as $k => $fieldName) {
//
//                if (isset($data['addonValues'][$k]) && $data['addonValues'][$k] != '') {
//                    $values[] = "( '" . intval($id) . "', '" . $mysql->escape($fieldName) .
//                                "', '" . $mysql->escape($data['addonValues'][$k]) . "' )";
//                }
//            }
//            if (!empty($values)) {
//                $query[] = "INSERT INTO `user_field` (`user`, `name`, `value`) VALUES " . implode(', ', $values);
//                $mysql->query($query);
//            }
//        }
//
//        return true;
//    }
//
}

