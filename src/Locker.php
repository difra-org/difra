<?php

namespace Difra;

/**
 * Class Locker
 * @package Difra
 *
 * Use locks to prevent race conditions.
 */
class Locker
{
    /** Cache prefix */
    const PREFIX = 'lock:';
    /** Lock try timeout, seconds */
    const TIMEOUT = 10;
    /** Prevent cache rewrite race condition delay, microseconds */
    const DELAY_S = 10000;
    /** Waiting for lock release delay, microseconds */
    const DELAY_L = 50000; // microseconds
    /** Maximum lock release wait time */
    const TTL = 5; // seconds

    /** @var string */
    private $key = null;
    /** @var int */
    private $rnd = null;

    /**
     * Lock
     * @param string $skey
     * @return Locker
     * @throws \Exception
     */
    public static function create($skey)
    {
        $rnd = mt_rand(100000000, 999999999);
        $key = self::PREFIX . $skey;
        $cache = Cache::getInstance();
        if ($cache->adapter == Cache::INST_NONE) {
            return new self();
        }
        $started = microtime(true);
        while (1) {
            // check lock
            $state = $cache->get($key);
            if (!$state) { // no lock - try to acquire
                $cache->put($key, $rnd, self::TTL);
                usleep(self::DELAY_S);
            } elseif ($state == $rnd) { // got lock
                $lock = new self;
                $lock->key = $key;
                $lock->rnd = $rnd;
                return $lock;
            } else { // locked by other process
                usleep(self::DELAY_L);
            }
            // check for time out
            if (microtime(true) - $started > self::TIMEOUT) {
                throw new \Exception('Failed to make lock');
            }
        }
    }

    /**
     * Unlock
     */
    public function remove()
    {
        if ($this->key and Cache::getInstance()->get($this->key) == $this->rnd) {
            Cache::getInstance()->remove($this->key);
            $this->key = null;
        }
    }

    /**
     * Locker constructor.
     */
    private function __construct()
    {
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->remove();
    }
}
