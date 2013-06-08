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

	public function test_find_index() {

		$action = new \Difra\Action;
		$action->uri = '';
		$action->find();
		$this->assertEquals( $action->className, 'IndexController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_adm() {

		$action = new \Difra\Action;
		$action->uri = 'adm';
		$action->find();
		$this->assertEquals( $action->className, 'AdmIndexController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_admDevConfig() {

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_admDevConfigReset() {

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config/reset';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->method, 'resetAction' );
	}
}