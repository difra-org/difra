<?php

require_once( dirname( __FILE__ ) . '/../fw/lib/autoloader.php' );

require_once( dirname( __FILE__ ) . '/framework.php' );
require_once( dirname( __FILE__ ) . '/plugins.php' );

class AllTests {

	public static function suite() {

		$suite = new PHPUnit_Framework_TestSuite( 'All' );
		$suite->addTestSuite( FrameworkSuite::suite() );
		$suite->addTestSuite( PluginsSuite::suite() );
		return $suite;
	}
}

