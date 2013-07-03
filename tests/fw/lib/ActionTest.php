<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_getInstance() {

		$action1 = \Difra\Action::getInstance();
		$action2 = \Difra\Action::getInstance();
		$this->assertSame( $action1, $action2 );
	}

	/**
	 * @backupGlobals enabled
	 */
	public function test_getUri() {

		$_SERVER['REQUEST_URI'] = '/normal/request/uri';
		$action = new \Difra\Action;
		$this->assertEquals( $action->getUri(), 'normal/request/uri' );

		$_SERVER['REQUEST_URI'] = '/normal/request/uri?some=strange?request';
		$action = new \Difra\Action;
		$this->assertEquals( $action->getUri(), 'normal/request/uri' );

		$_SERVER['URI'] = '/webserver/path';
		$action = new \Difra\Action;
		$this->assertEquals( $action->getUri(), 'webserver/path' );
	}

	public function test_getUri_Fail() {

		$this->setExpectedException( 'Difra\Exception' );
		$action = new \Difra\Action;
		$action->getUri();
	}

	public function test_find_IndexIndex() {

		$action = new \Difra\Action;
		$action->uri = '';
		$action->find();
		$this->assertEquals( $action->className, 'IndexController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		$action->uri = 'adm';
		$action->find();
		$this->assertEquals( $action->className, 'AdmIndexController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config/reset';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->methodAjax, 'resetAjaxAction' );
	}
}