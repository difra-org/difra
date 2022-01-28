<?php

declare(strict_types=1);

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
    /** @var string|null */
    private ?string $email = null;
    /** @var mixed */
    private mixed $data = null;

    /**
     * Singleton
     * @return Auth
     */
    public static function getInstance(): Auth
    {
        static $instance = null;
        return $instance ?? $instance = new self();
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
     * @param \DOMElement $node
     */
    public function getAuthXML(\DOMElement $node): void
    {
        $authNode = $node->appendChild($node->ownerDocument->createElement('auth'));
        if (!$this->email) {
            $authNode->appendChild($node->ownerDocument->createElement('unauthorized'));
            return;
        }
        /** @var \DOMElement $subNode */
        $subNode = $authNode->appendChild($node->ownerDocument->createElement('authorized'));
        $subNode->setAttribute('email', $this->email);
        $subNode->setAttribute('login', $this->getLogin());
        $subNode->setAttribute('id', $this->getUserId());
    }

    /**
     * Log in
     * @param string $email
     * @param array|null $data
     */
    public function login(string $email, array $data = null)
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
        @session_regenerate_id(true);
        if ($this->isAuthorized()) {
            $_SESSION['auth'] = [
                'email' => $this->email,
                'data' => $this->data
            ];
        } elseif (isset($_SESSION['auth'])) {
            unset($_SESSION['auth']);
        }
    }

    /**
     * Get auth data from session
     * @return void
     */
    private function load(): void
    {
        if (!isset($_SESSION['auth'])) {
            return;
        }
        $this->email = $_SESSION['auth']['email'];
        $this->data = $_SESSION['auth']['data'];
    }

    /**
     * Get current user's e-mail.
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get current user's login.
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->data['login'] ?? null;
    }

    /**
     * Get user ID
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Get info array
     * @return array
     */
    public function getInfo(): array
    {
        return $this->data['info'] ?? [];
    }

    /**
     * Is user authorized?
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return (bool)$this->email;
    }

    /**
     * Throws exception if user is not authorized.
     * For fast authorization check in methods, when methodnameAuthAction is not what you want.
     * @throws \Difra\View\HttpError
     */
    public function required()
    {
        if (!$this->email) {
            throw new HttpError(401);
        }
    }
}
