<?php

include_once( __DIR__ . '/../CacheTest.inc' );

class CacheXcacheTest extends CacheTest {

	static $inst = \Difra\Cache::INST_XCACHE;
}