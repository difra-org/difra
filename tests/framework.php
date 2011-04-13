<?php

include_once( 'framework/mysql.php' );

class FrameworkSuite {

	public static function suite() {

		$suite = new PHPUnit_Framework_TestSuite( 'FrameworkTest' );
		$suite->addTestSuite( 't_MySQL' );
		return $suite;
	}
}
