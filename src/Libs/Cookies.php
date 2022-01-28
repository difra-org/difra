<?php

declare(strict_types=1);

namespace Difra\Libs;

use Difra\Envi;
use Difra\Locales;
use JetBrains\PhpStorm\Pure;

/**
 * Cookies
 * @desc    Работа с куками
 * @package fw
 * @version 0.1
 * @access  public
 */
class Cookies
{
    /** @var int Cookie expiration */
    private int $expireTime = 0;
    /** @var string|null Cookie domain */
    private ?string $domain;
    /** @var string|null Cookie path */
    private ?string $path;

    /**
     * Constructor
     */
    #[Pure]
    private function __construct()
    {
        $this->domain = '.' . Envi::getHost(true);
        $this->path = '/';
    }

    /**
     * Singleton
     * @return Cookies
     */
    public static function getInstance(): Cookies
    {
        static $instance = null;
        return $instance ?? $instance = new self();
    }

    /**
     * Set cookies path
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Set cookies domain
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Set cookies expire time
     * @param int $expireTime
     */
    public function setExpire(int $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    /**
     * Remove cookie
     * @param string $name
     * @return bool
     */
    public function remove(string $name): bool
    {
        return setrawcookie($name, '', time() - 108000, $this->path, $this->domain);
    }

    /**
     * Sets cookie that makes Ajaxer show notification popup
     * @param string $message
     * @param bool|string $error
     * @throws \Difra\Exception
     */
    public function notify(string $message, bool|string $error = false)
    {
        if ($error === false) {
            $error = 'ok';
        } elseif ($error === true) {
            $error = 'error';
        }
        $this->set(
            'notify',
            [
                'type' => $error,
                'message' => $message,
                'lang' => [
                    'close' => Locales::get('notifications/close')
                ]
            ]
        );
    }

    /**
     * Set cookie
     * @param string $name
     * @param array|string $value
     * @return bool
     */
    public function set(string $name, array|string $value): bool
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        return setrawcookie($name, rawurlencode($value), $this->expireTime, $this->path, $this->domain);
    }

    /**
     * Set Ajaxer.js request cookie
     * @param $url
     */
    public function query($url): void
    {
        $this->set('query', ['url' => $url]);
    }
}
