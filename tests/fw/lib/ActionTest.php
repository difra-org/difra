<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_find_IndexIndex() {

		$action = new \Difra\Action;
		\Difra\Envi::setUri( '' );
		$action->find();
		$this->assertEquals( $action->className, 'IndexController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		\Difra\Envi::setUri( 'adm' );
		$action->find();
		$this->assertEquals( $action->className, 'AdmIndexController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		\Difra\Envi::setUri( 'adm/development/config' );
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->method, 'indexAction' );

		$action = new \Difra\Action;
		\Difra\Envi::setUri( 'adm/development/config/reset' );
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->methodAjax, 'resetAjaxAction' );
	}
}