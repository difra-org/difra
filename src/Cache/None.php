<?php

namespace Difra\Cache;

use Difra\Cache;

/**
 * Stub cache adapter
 * Class None
 * @package Difra\Cache
 */
class None extends Common
{
    /** @var string Adapter name */
    public ?string $adapter = Cache::INST_NONE;

    /**
     * Stub backend is always available. Or not.
     * Depends on your point of view, but let adapter be available anyways.
     * @return bool
     */
    public static function isAvailable()
    {
        return true;
    }

    /**
     * Get cache record pseudo-implementation
     * @param string $id
     * @param boolean $doNotTestCacheValidity
     * @return string
     */
    public function realGet(string $id, $doNotTestCacheValidity = false)
    {
        return null;
    }

    /**
     * Test if cache record exists pseudo-implementation
     * @param string $id cache id
     * @return boolean
     */
    public function test(string $id)
    {
        return false;
    }

    /**
     * Set cache record pseudo-implementation
     * @param string $id
     * @param string $data
     * @param bool|int $specificLifetime
     * @return boolean
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return false;
    }

    /**
     * Delete cache record pseudo-implementation
     * @param string $id
     * @return boolean
     */
    public function realRemove(string $id)
    {
        return false;
    }

    /**
     * Let it be
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
