<?php

class t_MySQL extends PHPUnit_Framework_TestCase {

	public function setUp() {

		$_SERVER['VHOST_DEVMODE'] = 'on';
		// load site
		Site::getInstance();
		// load plugins
		Plugger::getInstance();
	}

	public function testOne() {

		$this->assertTrue( true );
	}

	public function testTwo() {

		$this->assertFalse( false );
	}
}
