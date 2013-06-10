<?php

class MinifyTest extends PHPUnit_Framework_TestCase {

	public function test_js() {

		$test = \Difra\Minify::getInstance( 'js' )->minify( <<<EOT

var test = {};

test.test = function( e ) {
	test.testValue = e;
	testValue2 = 'abcd\'ed';
	/* testValue3 = 'dcba'; // 8 * 5 ? */
	testValue3 = 3 - 4 + // test
	( 5 - 7 ) / 1;
	testValue4
	= '';
	testValue4 = testValue4.(/[\\\\]/);
	return Form.Validator.getValidator('IsEmpty').test(element) || (/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));};

EOT
		);
		$this->assertEquals( $test,
			<<<EOT

var test={};test.test=function(e){test.testValue=e;testValue2='abcd\\'ed';testValue3=3-4+
(5-7)/1;testValue4='';testValue4=testValue4.(/[\\\\]/);return Form.Validator.getValidator('IsEmpty').test(element)||(/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));};
EOT
		);
	}

	public function test_InvalidJS() {

		$this->setExpectedException( 'Difra\Exception' );
		\Difra\Minify::getInstance( 'js' )->minify( 'var ab = \'' );
	}

	public function test_InvalidJS2() {

		$this->setExpectedException( 'Difra\Exception' );
		\Difra\Minify::getInstance( 'js' )->minify( 'var ab = /* djaisjdio' );
	}

	public function test_InvalidJS3() {

		$this->setExpectedException( 'Difra\Exception' );
		\Difra\Minify::getInstance( 'js' )->minify( "return t = testValue4.(/^)" );
	}

	public function test_InvalidJS4() {

		$this->setExpectedException( 'Difra\Exception' );
		\Difra\Minify::getInstance( 'js' )->minify( "return t = testValue4.(/[abc/);" );
	}

	public function test_css() {

		$test = \Difra\Minify::getInstance( 'css' )->minify( <<<EOT
html {
	min-width: 980px;
}

body {
	/* test */
	position: absolute;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
}

EOT
		);
		$this->assertEquals( $test, "html{min-width:980px;}body{position:absolute;left:0;right:0;top:0;bottom:0;}" );
	}

	public function test_none() {

		$test = <<<EOT
var test = {};
test.test = function( e ) {
	test.testValue = e;
};

EOT;
		$test2 = \Difra\Minify::getInstance( 'none' )->minify( $test );
		$this->assertEquals( $test, $test2 );
	}
}
