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
    const ACTIVATE_TTL = 7; // days
    const IP_MASK = '255.255.0.0'; // маска проверки ip

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
//    const ACTIVATE_NOTFOUND = 'activate_notfound';
//    const ACTIVATE_USED = 'activate_used';
//
//    public function activate($key)
//    {
//        $key = trim($key);
//        $db = MySQL::getInstance();
//        $data = $db->fetch("SELECT * FROM `user` WHERE `activation`='" . $db->escape($key) . "'");
//        if (empty($data)) {
//            return static::ACTIVATE_NOTFOUND;
//        }
//        $data = $data[0];
//        if ($data['active']) {
//            return static::ACTIVATE_USED;
//        }
//        $db->query("UPDATE `user` SET `active`='1' WHERE `activation`='" . $db->escape($key) . "'");
//        return true;
//    }
//
//
//    const PW_EMPTY = 'pw_empty';
//    const PW_SHORT = 'pw_short';
//    const PW_DIFF = 'pw_diff';
//
//    /**
//     * Устанавливает новый пароль пользователю
//     * @param int $user
//     * @param string $pw1
//     * @param string $pw2
//     * @return boolean
//     */
//    public function setPassword($user, $pw1, $pw2)
//    {
//        $pw1 = trim($pw1);
//        $pw2 = trim($pw2);
//        if (empty($pw1)) {
//            return self::PW_EMPTY;
//        }
//        if (strlen($pw1) < self::MIN_PASSWORD_LENGTH) {
//            return self::PW_SHORT;
//        }
//        if ($pw1 != $pw2) {
//            return self::PW_DIFF;
//        }
//        $auth = Auth::getInstance();
//        if ($auth->logged and $auth->getId() == $user) {
//            $auth->data['password'] = sha1($pw1);
//            $auth->update();
//        }
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `password`='" . sha1($pw1) . "' WHERE `id`='" . intval($user) . "'");
//        return true;
//    }
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
//    public function verifyPassword($password)
//    {
//        return sha1($password) == Auth::getInstance()->data['password'];
//    }
//
//    public function setInfo($data)
//    {
//        $auth = Auth::getInstance();
//        if (!$auth->logged) {
//            return false;
//        }
//        $db = MySQL::getInstance();
//        $data = $db->escape(serialize($data));
//        // TODO: info???
//        $db->query("UPDATE `user` SET `info`='$data' WHERE `id`='" . $db->escape($auth->data['id']) . "'");
//        $auth->data['info'] = $data;
//        $auth->update();
//        return true;
//    }
//
//    public function getInfo()
//    {
//        $auth = Auth::getInstance();
//        if (!$auth->logged) {
//            return false;
//        }
//        return @unserialize($auth->data['info']);
//    }
//
//    /**
//     * @param \DOMElement|\DOMNode $node
//     */
//    public function getInfoXML($node)
//    {
//        /** @var \DOMElement|\DOMNode $infoNode */
//        $infoNode = $node->appendChild($node->ownerDocument->createElement('userInfo'));
//        $data = $this->getInfo();
//        if (!empty($data)) {
//            foreach ($data as $k => $v) {
//                $infoNode->setAttribute($k, $v);
//            }
//        }
//    }
//
//    /**
//     * Возвращает xml со списком всех пользователей
//     * @param \DOMElement|\DOMNode $node
//     * @param int $page
//     * @param int $perPage
//     */
//    public function getListXML($node, $page = 1, $perPage = 75)
//    {
//        $db = MySQL::getInstance();
//        $rawTotal = $db->fetchRow("SELECT COUNT(`id`) AS `count` FROM `user`");
//        $total = intval($rawTotal['count']);
//        $first = ($page - 1) * $perPage;
//
//        $node->setAttribute('total', $total);
//        $node->setAttribute('first', $first);
//        $node->setAttribute('last', $first + $perPage);
//        $node->setAttribute('pages', floor((($total - 1) / $perPage) + 1));
//
//        $db->fetchXML(
//            $node,
//            "SELECT `id`,`email`,`active`,`banned`,`registered`,`lastseen`,`info`, `moderator` FROM `user` LIMIT {$first}, {$perPage}"
//        );
//    }
//
//    public function getUserXML(\DOMNode $node, $id)
//    {
//        $mysql = MySQL::getInstance();
//        $mysql->fetchXML(
//            $node,
//            'SELECT `id`,`email`,`active`,`banned`,`registered`,`lastseen`,`info`,`moderator` FROM `user` WHERE `id`=' .
//            $mysql->escape($id)
//        );
//
//        // вывод дополнительных полей
//        $query = "SELECT * FROM `user_field` WHERE `user`='" . intval($id) . "'";
//        $res = $mysql->fetch($query);
//        if (!empty($res)) {
//            $addonNode = $node->appendChild($node->ownerDocument->createElement('addon_fields'));
//
//            foreach ($res as $k => $data) {
//                /** @var \DOMElement $fieldItem */
//                $fieldItem = $addonNode->appendChild($node->ownerDocument->createElement('field'));
//                $fieldItem->setAttribute('name', $data['name']);
//                $fieldItem->setAttribute('value', $data['value']);
//            }
//        }
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
//    // проверяет поля unique на дубликаты в базе
//    public function checkUniqueFields($field, $data)
//    {
//        $conf = Config::getInstance()->get('users');
//        if (!$conf) {
//            return false;
//        }
//        if (!isset($conf['fields']) or empty($conf['fields'])) {
//            return false;
//        }
//        if (!isset($conf['fields'][$field]) || $conf['fields'][$field] != 'unique') {
//            return false;
//        }
//        // проверяем поле
//        $mysql = MySQL::getInstance();
//        $res = $mysql->fetch(
//            'SELECT `user` FROM `user_field` WHERE `name`=\'' . $mysql->escape($field) .
//            '\' AND `value`=\'' . $mysql->escape($data) . '\''
//        );
//        return !empty($res) ? true : false;
//    }
//
//    public function ban($id)
//    {
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `banned`=1 WHERE `id` = '" . intval($id) . "'");
//    }
//
//    public function unBan($id)
//    {
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `banned`=0 WHERE `id` = '" . intval($id) . "'");
//    }
//
//    public function setModerator($id)
//    {
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `moderator`=1 WHERE `id` = '" . intval($id) . "'");
//    }
//
//    public function unSetModerator($id)
//    {
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `moderator`=0 WHERE `id` = '" . intval($id) . "'");
//    }
//
//
//    /**
//     * Возвращает id юзера по его активации
//     * @param $code
//     * @return bool
//     */
//    public function getUserIdByActivation($code)
//    {
//        $db = MySQL::getInstance();
//        $r = $db->fetchRow("SELECT `id` FROM `user` WHERE `activation`='" . $db->escape($code) . "' AND `active`=1");
//        return isset($r['id']) ? $r['id'] : false;
//    }
//
//    /**
//     * Активирует пользоватля. Для ручной активации из админки
//     * @param $id
//     */
//    public function manualActivation($id)
//    {
//        $db = MySQL::getInstance();
//        $db->query("UPDATE `user` SET `active`=1, `activation`=NULL WHERE `id`='" . intval($id) . "'");
//    }

}

