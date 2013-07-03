<?php

abstract class CacheTest extends PHPUnit_Framework_TestCase {

	public function testCache() {

		try {
			$cache = \Difra\Cache::getInstance( static::$inst );
		} catch( \Difra\Exception $e ) {
			$this->markTestIncomplete( 'Can\'t test "' . static::$inst . '" cache, it\'s not available' );
		}
	}
}