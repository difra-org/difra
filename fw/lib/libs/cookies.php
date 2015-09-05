<?php

namespace Difra\Libs;

use Difra\Envi;
use Difra\Locales;

/**
 * Cookies
 * @desc    Работа с куками
 * @package fw
 * @version 0.1
 * @access  public
 */
class Cookies
{
    private $expireTime = 0;
    private $domain = null;
    private $path = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->domain = Envi::getHost(true);
        $this->domain = (substr($this->domain, 0, 4) == 'www.') ? substr($this->domain, 3) : ('.' . $this->domain);
        $this->path = '/';
    }

    /**
     * Singleton
     * @return Cookies
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Set cookies path
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Set cookies domain
     * @param string $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Set cookies expire time
     * @param integer $expireTime
     * @return void
     */
    public function setExpire($expireTime)
    {
        $this->expireTime = $expireTime;
    }

    /**
     * Remove cookie
     * @param string $name
     * @return boolean
     */
    public function remove($name)
    {
        return setrawcookie($name, '', time() - 108000, $this->path, $this->domain);
    }

    /**
     * Sets cookie that makes Ajaxer show notification popup
     * @param      $message
     * @param bool $error
     */
    public function notify($message, $error = false)
    {
        $this->set(
                'notify',
                [
                        'type' => $error ? 'error' : 'ok',
                        'message' => (string)$message,
                        'lang' => [
                                'close' => Locales::get('notifications/close')
                        ]
                ]
        );
    }

    /**
     * Set cookie
     * @param string $name
     * @param string|array $value
     * @return boolean
     */
    public function set($name, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        return setrawcookie($name, rawurlencode($value), $this->expireTime, $this->path, $this->domain);
    }

    /**
     * Set Ajaxer.js request cookie
     * @param $url
     * @return void
     */
    public function query($url)
    {
        $this->set('query', ['url' => $url]);
    }
}
