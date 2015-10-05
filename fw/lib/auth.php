<?php

namespace Difra;

use Difra\Envi\Session;
use Difra\View\HttpError;

/**
 * Class Auth
 * Auth adapter. User logins, logouts and user sessions.
 * This is a layer between actual auth and framework code.
 * It allows to write any auth plugins without direct calls to them and without registering multiple boring handlers.
 * @package Difra
 */
class Auth
{
    /** @var string */
    private $email = null;
    /** @var mixed[] */
    private $data = null;

    /**
     * Singleton
     * @return Auth
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * Get auth data as XML node
     * @param \DOMNode|\DOMElement $node
     */
    public function getAuthXML($node)
    {
        $authNode = $node->appendChild($node->ownerDocument->createElement('auth'));
        if (!$this->email) {
            $authNode->appendChild($node->ownerDocument->createElement('unauthorized'));
            return;
        } else {
            /** @var \DOMElement $subNode */
            $subNode = $authNode->appendChild($node->ownerDocument->createElement('authorized'));
            $subNode->setAttribute('email', $this->email);
            $subNode->setAttribute('login', $this->getLogin());
            $subNode->setAttribute('id', $this->getUserId());
        }
    }

    /**
     * Log in
     * @param string $email
     * @param array $data
     */
    public function login($email, $data = null)
    {
        $this->email = $email;
        $this->data = $data;
        $this->save();
    }

    /**
     * Log out
     */
    public function logout()
    {
        $this->email = $this->data = null;
        $this->save();
    }

    /**
     * Update user session
     */
    public function update()
    {
        $this->save();
    }

    /**
     * Save auth data in session
     */
    private function save()
    {
        Session::start();
        if ($this->email) {
            $_SESSION['auth'] = [
                'id' => $this->email,
                'data' => $this->data
            ];
        } else {
            if (isset($_SESSION['auth'])) {
                unset($_SESSION['auth']);
            }
        }
    }

    /**
     * Get auth data from session
     * @return bool
     */
    private function load()
    {
        if (!isset($_SESSION['auth'])) {
            return false;
        }
        $this->email = $_SESSION['auth']['id'];
        $this->data = $_SESSION['auth']['data'];
        return true;
    }

    /**
     * Get current user's e-mail.
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get current user's login.
     * @return mixed|null
     */
    public function getLogin()
    {
        return isset($this->data['login']) ? $this->data['login'] : null;
    }

    /**
     * Get user ID
     * @return mixed|null
     */
    public function getUserId()
    {
        return isset($this->data['id']) ? $this->data['id'] : null;
    }

    /**
     * Get info array
     * @return mixed
     */
    public function getInfo()
    {
        return $this->data['info'];
    }

    /**
     * Is user authorized?
     * @return bool
     */
    public function isLogged()
    {
        return (bool)$this->email;
    }

    /**
     * Throws exception if user is not authorized.
     * For fast authorization check in methods, when methodnameAuthAction is not what you want.
     */
    public function required()
    {
        if (!$this->email) {
            throw new HttpError(401);
        }
    }
}
