<?php

namespace Difra\Plugins\Users;

use Difra\Exception;
use Difra\PDO;

class User
{
    const DB = 'users';

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

    /** @var array[] */
    private $modified = [];

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

    private function save()
    {
        if (empty($this->modified)) {
            return;
        }
        $set = [];
        $parameters = [];
        foreach ($this->modified as $field => $value) {
            if (!property_exists($this, $field)) {
                throw new Exception("Invalid user property: $field");
            }
            switch ($field) {
                default:
                    $set[] = "`$field` = :$field";
                    $parameters[$field] = $value;
            }
        }
        if (empty($set)) {
            return;
        }
        $pdo = PDO::getInstance(self::DB);
        if ($this->id) {
            $parameters['id'] = $this->id;
            $pdo->query("\nUPDATE `user` SET " . implode(',', $set) . ' WHERE `id`=:id', $parameters);
        } else {
            $pdo->query('INSERT INTO `user` SET ' . implode(',', $set));
        }
        $this->modified = [];
    }

    /**
     * Initialize User object from database row data
     * @param array $data Database row
     * @return User
     */
    private static function load($data = [])
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
        $users = self::getList($paginator);
        foreach ($users as $user) {
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
        $pdo = PDO::getInstance(self::DB);
        if ($paginator) {
            $limits = $paginator->getPaginatorLimit();
            $usersData = $pdo->fetch("SELECT * FROM `user` LIMIT {$limits[0]},{$limits[1]}");
            $total = $pdo->fetchOne('SELECT COUNT(*) FROM `user`');
            $paginator->setTotal($total);
        } else {
            $usersData = $pdo->fetch('SELECT * FROM `user`');
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
}