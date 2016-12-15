<?php

namespace Difra\Cache;

use Difra\Cache;
use Difra\Envi;
use Difra\Exception;

/**
 * Abstract cache adapter class
 * Class Common
 * @package Difra\Cache
 */
abstract class Common
{
    //abstract static public function isAvailable();

    /** @var string */
    public $adapter = null;

    /** @var string Version for cache */
    private $version = null;
    /** @var string Cache prefix */
    private $prefix = null;
    /** @var string Session prefix */
    private $sessionPrefix = 'session:';

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!method_exists($this, 'isAvailable') or !$this::isAvailable()) {
            throw new Exception(__CLASS__ . ' requested, but that cache is not available!');
        }
        $this->version = Envi\Version::getBuild();
        $this->prefix = Envi::getSubsite() . ':';
        $this->sessionPrefix = 'session:';
    }

    /**
     * Check if cache record exists
     * @deprecated
     * @param string $id
     * @return bool
     */
    abstract public function test($id);

    /**
     * Defines if cache backend supports automatic cleaning
     * @return bool
     */
    abstract public function isAutomaticCleaningAvailable();

    /**
     * Get cache record wrapper
     * @param $key
     * @param bool $versionCheck Check if version number changed
     * @return null|string
     */
    public function get($key, $versionCheck = true)
    {
        $data = $this->realGet($this->prefix . $key);
        if (
            !$data
            or
            !isset($data['expires']) or $data['expires'] < time()
            or
            (
                $versionCheck and (
                    !isset($data['version'])
                    or
                    $data['version'] != $this->version
                )
            )
        ) {
            return null;
        }
        return $data['data'];
    }

    /**
     * Get cache record
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed|null
     */
    abstract public function realGet($id, $doNotTestCacheValidity = false);

    /**
     * Set cache record wrapper
     * @param string $key
     * @param string $data
     * @param int $ttl
     */
    public function put($key, $data, $ttl = 300)
    {
        $data = [
            'expires' => time() + $ttl,
            'data' => $data,
            'version' => $this->version
        ];
        $this->realPut($this->prefix . $key, $data, $ttl);
    }

    /**
     * Set cache record
     * @param string $id
     * @param mixed $data
     * @param bool $specificLifetime
     */
    abstract public function realPut($id, $data, $specificLifetime = false);

    /**
     * Delete cache record wrapper
     * @param string $key
     */
    public function remove($key)
    {
        $this->realRemove($this->prefix . $key);
    }

    /**
     * Delete cache method
     * @param string $id
     */
    abstract public function realRemove($id);

    /**
     * Set session handler to use current cache, if available
     */
    public function setSessionsInCache()
    {
        static $set = false;
        if ($set) {
            return;
        }
        if (Cache::getInstance()->adapter == Cache::INST_NONE) {
            return;
        }

        /** @noinspection PhpUnusedParameterInspection */
        session_set_save_handler(
        // open
            function ($s, $n) {
                return true;
            },
            // close
            function () {
                return true;
            },
            // read
            function ($id) {
                return Cache::getInstance()->get($this->sessionPrefix . $id, false) ?: '';
            },
            // write
            function ($id, $data) {
                if (!$data) {
                    return false;
                }
                Cache::getInstance()->put($this->sessionPrefix . $id, $data, 86400); // 24h
                return true;
            },
            // destroy
            function ($id) {
                Cache::getInstance()->remove($this->sessionPrefix . $id);
                return true;
            },
            // garbage collector
            function ($expire) {
                return true;
            }
        );
        $set = true;
    }
}
