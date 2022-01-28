<?php

declare(strict_types=1);

namespace Difra\Libs\Auth;

use Difra\Envi\Session;
use Difra\View\HttpError;

/**
 * Class Digest
 * Digest HTTP auth
 * @package Difra\Libs\Auth
 */
class Digest
{
    /** @var string Auth realm */
    public string $realm = 'Restricted area';
    /** @var array Users list */
    private array $users;
    /** @var bool Stale */
    private bool $stale = false;

    /**
     * Singleton
     * @param array $newUsers
     * @return Digest
     */
    public static function getInstance(array $newUsers = []): Digest
    {
        static $instance = null;
        if (!$instance) {
            $instance = new self($newUsers);
        }
        return $instance;
    }

    /**
     * Constructor
     * @param array $newUsers
     */
    public function __construct(array $newUsers = [])
    {
        $this->users = $newUsers;
    }

    /**
     * Request Digest auth
     * @throws \Difra\View\HttpError
     */
    public function request()
    {
        header('HTTP/1.1 401 Unauthorized');
        header(
            'WWW-Authenticate: Digest realm="' .
            $this->realm .
            '",qop="auth",nonce="' .
            $this->getNonce(true) .
            '",opaque="' .
            md5($this->realm) .
            '"' .
            ($this->stale ? ',stale=TRUE' : '')
        );

        throw new HttpError(401);
    }

    /**
     * Verify auth
     * @return bool
     */
    public function verify(): bool
    {
        if (!isset($_SERVER['PHP_AUTH_DIGEST'])
            or !$data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])
            or !isset($this->users[$data['username']])
        ) {
            return false;
        }

        if ($data['nonce'] != $this->getNonce() or !$this->checkNC($data['nc'])) {
            $this->stale = true;
            return false;
        }

        $A1 = md5($data['username'] . ':' . $this->realm . ':' . $this->users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5(
            $A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2
        );
        if ($data['response'] != $valid_response) {
            return false;
        }
        return true;
    }

    /**
     * Digest auth parser
     * @param $txt
     * @return array|null
     */
    private function httpDigestParse($txt): ?array
    {
        // protect against missing data
        $needed_parts =
            ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
        $data = [];

        // php docs use @(\w+)=(?:([\'"])([^\2]+)\2|([^\s,]+))@ regexp, but it doesn't work
        preg_match_all('@(\w+)=[\'"]?([^\s,\'"]+)@', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $data[$match[1]] = $match[2];
            unset($needed_parts[$match[1]]);
        }

        return !empty($needed_parts) ? null : $data;
    }

    /**
     * Get nonce
     * @param bool $regen Force new nonce
     * @return string|null
     */
    private function getNonce(bool $regen = false): ?string
    {
        Session::start();
        if ($regen) {
            $key = '';
            // TODO: correct safe random sequence
            for ($i = 0; $i < 16; $i++) {
                $key .= chr(rand(0, 255));
            }
            $_SESSION['digest_nonce'] = bin2hex($key);
            $_SESSION['digest_nc'] = 0;
        }
        return $_SESSION['digest_nonce'] ?? null;
    }

    /**
     * Check nc
     * @param $nc
     * @return bool
     */
    private function checkNC($nc): bool
    {
        Session::start();
        if (!isset($_SESSION['digest_nc']) or $_SESSION['digest_nc'] >= $nc) {
            return false;
        }
        $_SESSION['nc'] = $nc;
        return true;
    }
}
