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
}