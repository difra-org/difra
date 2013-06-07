<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_getInstance() {

		$action1 = \Difra\Action::getInstance();
		$action2 = \Difra\Action::getInstance();
		$this->assertEquals( $action1, $action2 );
	}

	public function test_getUri_Fail() {

		$this->setExpectedException( 'Difra\Exception' );
		$action = new \Difra\Action;
		$action->getUri();
	}

}