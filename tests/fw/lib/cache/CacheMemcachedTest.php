<?php

include_once( __DIR__ . '/../CacheTest.inc' );

class CacheMemcachedTest extends CacheTest {

	static $inst = \Difra\Cache::INST_MEMCACHED;
}