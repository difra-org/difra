<?php

include_once( __DIR__ . '/../CacheTest.inc' );

class CacheMemcacheTest extends CacheTest {

	static $inst = \Difra\Cache::INST_MEMCACHE;
}