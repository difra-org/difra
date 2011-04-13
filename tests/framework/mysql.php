<?php

class t_MySQL extends PHPUnit_Framework_TestCase {

	public function testInstance() {

		$db = MySQL::getInstance();
		$this->assertTrue(TRUE, 'This should already work.');
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}
}
