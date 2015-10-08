<?php

namespace Difra\Plugins\Users;

use Difra\DB;
use Difra\Mailer;
use Difra\Plugins\Users;

/**
 * Class Recover
 * @package Difra\Plugins\Users
 */
class Recover
{
    public static function send($login)
    {
        if (is_null($login) or $login === '' or $login === false) {
            return User::LOGIN_NOTFOUND;
        }
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow(
            'SELECT `id`,`email`,`active`,`banned` FROM `user` WHERE `email`=:login OR `login`=:login LIMIT 1',
            ['login' => $login]
        );
        if (empty($data)) {
            return User::LOGIN_NOTFOUND;
        }
        $data = $data[0];
        if (!$data['active']) {
            return User::LOGIN_INACTIVE;
        }
        if ($data['banned']) {
            return User::LOGIN_BANNED;
        }
        do {
            $key = bin2hex(openssl_random_pseudo_bytes(12));
            $d = $db->fetchOne('SELECT count(*) FROM `user_recover` WHERE `recover`=\'' . $key . "'");
        } while ($d);
        $db->query("INSERT INTO `user_recover` (`recover`,`user`) VALUES (?,?)", [$key, $data['id']]);
        Mailer::getInstance()->CreateMail(
            $data['email'], 'mail_recover', ['code' => $key, 'ttl' => Users::getRecoverTTL()]
        );
        return true;
    }

    const RECOVER_INVALID = 'recover_invalid';
    const RECOVER_USED = 'recover_used';
    const RECOVER_OUTDATED = 'recover_outdated';

    public static function verify($key, $returnUser = false)
    {
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetchRow('SELECT * FROM `user_recover` WHERE `recover`=?', [$key]);
        if (empty($data)) {
            return self::RECOVER_INVALID;
        }
        if ($data['used']) {
            return self::RECOVER_USED;
        }
        $date = $data['date_requested'];
        $date = explode(' ', $date);
        $day = explode('-', $date[0]);
        $time = explode(':', $date[1]);
        $day1 = mktime($time[0], $time[1], $time[2], $day[1], $day[2], $day[0]);
        if ($day1 and (time() - $day1 > 3600 * Users::RECOVER_TTL)) {
            return self::RECOVER_OUTDATED;
        }
        if (!$returnUser) {
            return true;
        }
        return User::getById($data['user']);
    }

    public static function setUsed($key)
    {
        if (!$key) {
            return;
        }
        DB::getInstance(Users::getDB())->query(
            'UPDATE `user_recover` SET `used`=1 WHERE `recover`=?',
            [(string)$key]
        );
    }

//    public function recoverSetPassword($key, $pw1, $pw2)
//    {
//        $db = MySQL::getInstance();
//        $data = $db->fetch("SELECT * FROM `user_recover` WHERE `user`='" . $db->escape($key) . "'");
//        if (empty($data)) {
//            return self::RECOVER_INVALID;
//        }
//        $data = $data[0];
//        if (($r = $this->setPassword($data['user_id'], $pw1, $pw2)) !== true) {
//            return $r;
//        }
//        $db->query(
//            'UPDATE `user_recover` SET `used`=1,`date_used`=NOW() WHERE `recover`=\'' . $db->escape($key) . "'"
//        );
//        return true;
//    }
}
