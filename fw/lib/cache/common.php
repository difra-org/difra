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

    const SESS_PREFIX = 'session:';
    /** @var string */
    public $adapter = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!method_exists($this, 'isAvailable') or !$this::isAvailable()) {
            throw new Exception(__CLASS__ . ' requested, but that cache is not available!');
        }
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
     * @return string|null
     */
    public function get($key)
    {
        $data = $this->realGet(Envi::getSubsite() . '_' . $key);
        if (!$data or !isset($data['expires']) or $data['expires'] < time()) {
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
                'data' => $data
        ];
        $this->realPut(Envi::getSubsite() . '_' . $key, $data, $ttl);
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
        $this->realRemove(Envi::getSubsite() . '_' . $key);
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
                    return Cache::getInstance()->get(self::SESS_PREFIX . $id) ?: '';
                },
                // write
                function ($id, $data) {
                    if (!$data) {
                        return false;
                    }
                    Cache::getInstance()->put(self::SESS_PREFIX . $id, $data, 86400); // 24h
                    return true;
                },
                // destroy
                function ($id) {
                    Cache::getInstance()->remove(self::SESS_PREFIX . $id);
                },
                // garbage collector
                function ($expire) {
                    return true;
                }
        );
        $set = true;
    }
}
