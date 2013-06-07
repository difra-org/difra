<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_getUri_Fail() {

		$this->setExpectedException( 'Difra\Exception' );
		$action = new \Difra\Action;
		$action->getUri();
	}
}