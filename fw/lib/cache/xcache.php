<?php

namespace Difra\Cache;

use Difra\Cache;
use Difra\Exception;

/**
 * XCache adapter
 * Class XCache
 * @package Difra\Cache
 */
class XCache extends Common
{
    public $adapter = Cache::INST_XCACHE;

    /**
     * Detect if backend is available
     * @return bool
     */
    public static function isAvailable()
    {
        try {
            if (!extension_loaded('xcache') or !ini_get('xcache.var_size') or php_sapi_name() == 'cli') {
                return false;
            }
            @xcache_isset('test');
            if ($e = error_get_last() and $e['file'] == __FILE__) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }

    /**
     * Get cache record implementation
     * @param string $id
     * @param boolean $doNotTestCacheValidity
     * @return string
     */
    public function realGet($id, $doNotTestCacheValidity = false)
    {
        if (xcache_isset($id)) {
            return xcache_get($id);
        }
        return null;
    }

    /**
     * Check if cache record exists implementation
     * @param string $id cache id
     * @return boolean
     */
    public function test($id)
    {
        return xcache_isset($id);
    }

    /**
     * Put cache record implementation
     * @param string $id
     * @param string $data
     * @param bool|int $specificLifetime
     * @return boolean
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return xcache_set($id, $data, $specificLifetime);
    }

    /**
     * Delete cache record implementation
     * @param string $id
     * @return boolean
     */
    public function realRemove($id)
    {
        return xcache_unset($id);
    }

    /**
     * Define automatic cleaning as available
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
