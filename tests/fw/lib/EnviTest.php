<?php

class EnviTest extends PHPUnit_Framework_TestCase {

	public function test_mode() {

		\Difra\Envi::setMode( 'web' );
		$mode = \Difra\Envi::getMode();
		$this->assertEquals( $mode, 'web' );
		\Difra\Envi::setMode( 'cli' );
		$mode = \Difra\Envi::getMode();
		$this->assertEquals( $mode, 'cli' );
		\Difra\Envi::setMode( 'include' );
		$mode = \Difra\Envi::getMode();
		$this->assertEquals( $mode, 'include' );
	}

	public function test_getHost() {

		$this->assertEquals( \Difra\Envi::getHost(), trim( `hostname` ) );
		$this->assertEquals( \Difra\Envi::getHost( true ), trim( `hostname` ) );
		$_SERVER['HTTP_HOST'] = 'www.example.com';
		$this->assertEquals( \Difra\Envi::getHost(), 'www.example.com' );
		$_SERVER['VHOST_MAIN'] = 'example.com';
		$this->assertEquals( \Difra\Envi::getHost( true ), 'example.com' );
		$this->assertEquals( \Difra\Envi::getHost(), 'www.example.com' );
	}
}