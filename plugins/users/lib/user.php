<?php

namespace Difra\Plugins\Users;

use Difra\Auth;
use Difra\Config;
use Difra\Exception;
use Difra\DB;
use Difra\Plugins\Users;

/**
 * Class User
 * @package Difra\Plugins\Users
 */
class User
{
    const LOGIN_NOTFOUND = 'not_found';
    const LOGIN_BANNED = 'banned';
    const LOGIN_INACTIVE = 'inactive';
    const LOGIN_BADPASS = 'bad_password';
    /** @var int */
    private $id = null;
    /** @var string */
    private $email = null;
    /** @var string */
    private $login = null;
    /** @var string SHA-1 */
    private $password = null;
    /** @var bool */
    private $active = false;
    /** @var bool */
    private $banned = false;
    /** @var string Datetime */
    private $registered = null;
    /** @var string Datetime */
    private $lastseen = null;
    /** @var mixed[] */
    private $info = [];
    /** @var string|null */
    private $activation = null;
    /** @var bool */
    private $modified = false;
    /** @var string[] */
    private $saveProperties = ['email', 'login', 'password', 'active', 'banned', 'info', 'activation'];
    /** @var self[] */
    private static $cache = [];

    /**
     * Forbid direct creation of user object
     */
    private function __construct()
    {
    }

    public function __destruct()
    {
        $this->save();
    }

    public function save()
    {
        if (!$this->modified) {
            return;
        }
        $set = [];
        $parameters = [];
        foreach ($this->saveProperties as $field) {
            switch ($field) {
                case 'info':
                    $set[] = "`$field` = :$field";
                    $parameters[$field] = serialize($this->{$field});
                    break;
                default:
                    $set[] = "`$field` = :$field";
                    $parameters[$field] = $this->{$field};
            }
        }
        $db = DB::getInstance(Users::DB);
        if ($this->id) {
            $parameters['id'] = $this->id;
            $db->query("\nUPDATE `user` SET " . implode(',', $set) . ' WHERE `id`=:id', $parameters);
        } else {
            $db->query('INSERT INTO `user` SET ' . implode(',', $set));
            $this->id = $db->getLastId();
            self::$cache[$this->id] = $this;
        }
        $this->modified = [];
    }

    /**
     * Initialize User object from database row data
     * @param array $data Database row
     * @return User
     */
    private static function load($data)
    {
        $user = new self;
        $user->id = $data['id'];
        $user->email = $data['email'];
        $user->login = $data['login'];
        $user->password = $data['password'];
        $user->active = $data['active'];
        $user->banned = $data['banned'];
        $user->registered = $data['registered'];
        $user->lastseen = $data['lastseen'];
        $user->info = $data['info'] ? @unserialize($data['info']) : '';
        $user->activation = $data['activation'];
        self::$cache[$user->id] = $user;
        return $user;
    }

    /**
     * @param \DOMElement $node
     * @param \Difra\Unify\Paginator $paginator
     * @param bool $createNode
     */
    public static function getListXML($node, $paginator, $createNode = false)
    {
        $subNode = $createNode ? $node->appendChild($node->ownerDocument->createElement('users')) : $node;
        $paginator->getPaginatorXML($subNode);
        foreach (self::getList($paginator) as $user) {
            $user->getXML($subNode, true);
        }
    }

    /**
     * @param \Difra\Unify\Paginator $paginator
     * @return self[]
     * @throws \Difra\Exception
     */
    public static function getList($paginator = null)
    {
        $db = DB::getInstance(Users::DB);
        if ($paginator) {
            $limits = $paginator->getPaginatorLimit();
            $usersData = $db->fetch("SELECT * FROM `user` LIMIT {$limits[0]},{$limits[1]}");
            $total = $db->fetchOne('SELECT COUNT(*) FROM `user`');
            $paginator->setTotal($total);
        } else {
            $usersData = $db->fetch('SELECT * FROM `user`');
        }
        $users = [];
        foreach ($usersData as $data) {
            $user = new self;
            $user->load($data);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * @param \DOMElement $node
     * @param bool|false $createNode
     */
    public function getXML($node, $createNode = false)
    {
        $subNode = $createNode ? $node->appendChild($node->ownerDocument->createElement('user')) : $node;
        $subNode->setAttribute('email', $this->email);
        $subNode->setAttribute('login', $this->login);
        $subNode->setAttribute('active', $this->active);
        $subNode->setAttribute('banned', $this->banned);
        $subNode->setAttribute('registered', $this->registered);
        $subNode->setAttribute('lastseen', $this->lastseen);
        if (!empty($this->info)) {
            /** @var \DOMElement $infoNode */
            $infoNode = $subNode->appendChild($subNode->ownerDocument->createElement('info'));
            foreach ($this->info as $k => $v) {
                $infoNode->setAttribute($k, $v);
            }
        }
    }

    /**
     * Get user ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user e-mail
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user e-mail
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->modified[] = 'email';
    }

    /**
     * Get user login
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set user login
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
        $this->modified[] = 'login';
    }

    /**
     * Get user's password hash
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = sha1($password);
        $this->modified[] = 'password';
    }

    /**
     * Is user activated?
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Is user banned?
     * @return boolean
     */
    public function isBanned()
    {
        return $this->banned;
    }

    /**
     * Set user banned flag
     * @param boolean $banned
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;
        $this->modified[] = 'banned';
    }

    /**
     * Get user registration date and time
     * @return string
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Get user's last seen flag
     * @return string
     */
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * Get user's additional info array
     * @return \mixed[]
     */
    public function &getInfo()
    {
        return $this->info;
    }

    /**
     * Update user's additional info array
     * @param \mixed[] $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Activate user by e-mail
     * @param $key
     * @return int
     * @throws Exception
     */
    public static function activate($key)
    {
        // prevent activation of all users with empty activation key
        if (!$key) {
            return false;
        }
        // find user
        $db = DB::getInstance(Users::DB);
        if (!$userId = $db->fetchOne('SELECT `id` FROM `user` WHERE `active`=0 AND `activation`=? LIMIT 1', [$key])) {
            // not found
            return false;
        }
        // activate
        $db->query('SET `active`=1,`activation`=NULL WHERE `id`=?', [$userId]);
        return $userId;
    }

    /**
     * Get user by id
     * @param $id
     * @return self
     * @throws Exception
     */
    public static function getById($id)
    {
        if (isset(self::$cache[$id])) {
            $user = self::$cache[$id];
        } else {
            $data = DB::getInstance(Users::getDB())->fetchRow('SELECT * FROM `user` WHERE `id`=?', [$id]) ?: false;
            $user = $data ? self::load($data) : false;
        }
        if (!$user) {
            throw new Exception(self::LOGIN_NOTFOUND);
        }

        return $user;
    }

    /**
     * Log in current user
     */
    public function login()
    {
        // check if user is banned
        if ($this->banned) {
            throw new Exception(self::LOGIN_BANNED);
        }
        // check if user is not active
        if (!$this->active) {
            throw new Exception(self::LOGIN_INACTIVE);
        }
        Auth::getInstance()->login($this->email, [
            'id' => $this->id,
            'login' => $this->login,
            'info' => $this->info
        ]);
    }

    /**
     * Log in user by email/login and password
     * @param string $login Email/login
     * @param string $password Password
     * @param bool $longSession Set long session
     * @return User
     * @throws Exception
     */
    public static function loginByPassword($login, $password, $longSession)
    {
        $data = DB::getInstance(Users::getDB())->fetchRow(<<<QUERY
SELECT * FROM `user` WHERE (`email` = :login OR `login` = :login)
QUERY
            , [
                'login' => $login,
                'password' => $password
            ]
        );
        if (empty($data)) {
            throw new Exception(self::LOGIN_NOTFOUND);
        }
        if ($data['password'] !== sha1($password) and $data['password'] !== md5($password)) {
            throw new Exception(self::LOGIN_BADPASS);
        }
        $user = self::load($data);
        $user->login();
        if ($longSession) {
            Session::save();
        }
        return $user;
    }

    /**
     * Log out current user
     */
    public static function logout()
    {
        Session::remove();
        Auth::getInstance()->logout();
    }

    /**
     * Create new user
     * @return User
     * @throws Exception
     */
    public static function create()
    {
        $user = new self;

        // configure activation
        $activation = Users::getActivationMethod();
        switch ($activation) {
            case 'email':
                do {
                    $user->activation = bin2hex(openssl_random_pseudo_bytes(24));
                } while (DB::getInstance(Users::getDB())
                           ->fetchOne('SELECT count(*) FROM user WHERE activation=?', [$user->activation]));
                $user->active = 0;
                break;
            case 'moderate':
                $user->activation = null;
                $user->active = 0;
                break;
            case 'none':
                $user->activation = null;
                $user->active = 1;
                break;
            default:
                throw new Exception("Unknown activation type: $activation");
        }
    }
}
