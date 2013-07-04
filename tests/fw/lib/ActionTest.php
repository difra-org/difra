<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_find_IndexIndex() {

		\Difra\Envi::setUri( '' );
		\Difra\Envi\Action::find();
		$this->assertEquals( \Difra\Envi\Action::$className, 'IndexController' );
		$this->assertEquals( \Difra\Envi\Action::$method, 'indexAction' );

		\Difra\Envi::setUri( 'adm' );
		\Difra\Envi\Action::find();
		$this->assertEquals( \Difra\Envi\Action::$className, 'AdmIndexController' );
		$this->assertEquals( \Difra\Envi\Action::$method, 'indexAction' );

		\Difra\Envi::setUri( 'adm/development/config' );
		\Difra\Envi\Action::find();
		$this->assertEquals( \Difra\Envi\Action::$className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( \Difra\Envi\Action::$method, 'indexAction' );

		\Difra\Envi::setUri( 'adm/development/config/reset' );
		\Difra\Envi\Action::find();
		$this->assertEquals( \Difra\Envi\Action::$className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( \Difra\Envi\Action::$methodAjax, 'resetAjaxAction' );
	}
}