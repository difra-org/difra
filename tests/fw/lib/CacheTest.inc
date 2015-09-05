<?php

abstract class CacheTest extends PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        try {
            $cache = \Difra\Cache::getInstance(static::$inst);
        } catch (\Difra\Exception $e) {
            $this->markTestSkipped('Can\'t test "' . static::$inst . '" cache, it\'s not available');
            return;
        }
        $key = 'test_' . rand('100000', '999999');
        $value = 'value_' . rand('100000', '999999');
        $cache->put($key, $value);
        $this->assertEquals($value, $cache->get($key));
        $cache->remove($key);
        $this->assertNull($cache->get($key));
    }
}
