<?php

namespace Difra\Cache;

use Difra\Cache;

/**
 * Class APCu
 * @package Difra\Cache
 */
class APCu extends Common
{
    /** @var string Adapter name */
    public $adapter = Cache::INST_APCU;

    /**
     * Is APCu available?
     * @return bool
     */
    public static function isAvailable()
    {
        try {
            if (!extension_loaded('apcu') or php_sapi_name() == 'cli' or !function_exists('apcu_sma_info')) {
                return false;
            }
            $info = @apcu_sma_info(true);
            if ($e = error_get_last() and $e['file'] == __FILE__) {
                return false;
            }
            if (empty($info) or empty($info['num_seg'])) {
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }
        return true;
    }

    /**
     * Check if cache record exists
     * @deprecated
     * @param string $id
     * @return bool
     */
    public function test($id)
    {
        return apcu_exists($id);
    }

    /**
     * Defines if cache backend supports automatic cleaning
     * @return bool
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }

    /**
     * Get cache record
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed|null
     */
    public function realGet($id, $doNotTestCacheValidity = false)
    {
        $success = false;
        $value = apcu_fetch($id, $success);
        return $success ? $value : null;
    }

    /**
     * Set cache record
     * @param string $id
     * @param mixed $data
     * @param bool $specificLifetime
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        apcu_store($id, $data, $specificLifetime ?: Cache::DEFAULT_TTL);
    }

    /**
     * Delete cache method
     * @param string $id
     */
    public function realRemove($id)
    {
        apcu_delete($id);
    }
}
