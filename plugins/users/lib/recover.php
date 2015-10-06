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
    public function recover($email)
    {
        $db = DB::getInstance(Users::getDB());
        $data = $db->fetch('SELECT * FROM `user` WHERE `email`=?', [$email]);
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
            $d = $db->fetch('SELECT `recover` FROM `user_recover` WHERE `recover`=\'' . $key . "'");
        } while (!empty($d));
        $db->query("INSERT INTO `user_recover` (`recover`,`user`) VALUES ('$key','{$data['id']}')");
        Mailer::getInstance()->CreateMail(
            $data['email'], 'mail_recover', ['code' => $key, 'ttl' => Users::getRecoverTTL()]
        );
        return true;
    }

    const RECOVER_INVALID = 'recover_invalid';
    const RECOVER_USED = 'recover_used';
    const RECOVER_OUTDATED = 'recover_outdated';

    public function verifyRecover($key)
    {
        $db = DB::getInstance();
        $data = $db->fetch("SELECT * FROM `user_recover` WHERE `recover`='" . $db->escape($key) . "'");
        if (empty($data)) {
            return self::RECOVER_INVALID;
        }
        $data = $data[0];
        if ($data['used']) {
            return self::RECOVER_USED;
        }
        $date = $data['date_requested'];
        $date = explode(' ', $date);
        $day = explode('-', $date[0]);
        $time = explode(':', $date[1]);
        $day1 = mktime($time[0], $time[1], $time[2], $day[1], $day[2], $day[0]);
        if ($day1 and (time() - $day1 > 1440 * 60 * 3)) {
            return self::RECOVER_OUTDATED;
        }
        return true;
    }

    public function recoverSetPassword($key, $pw1, $pw2)
    {
        $db = MySQL::getInstance();
        $data = $db->fetch("SELECT * FROM `user_recover` WHERE `user`='" . $db->escape($key) . "'");
        if (empty($data)) {
            return self::RECOVER_INVALID;
        }
        $data = $data[0];
        if (($r = $this->setPassword($data['user_id'], $pw1, $pw2)) !== true) {
            return $r;
        }
        $db->query(
            'UPDATE `user_recover` SET `used`=1,`date_used`=NOW() WHERE `recover`=\'' . $db->escape($key) . "'"
        );
        return true;
    }
}
