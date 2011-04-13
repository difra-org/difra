<?php

class PluginsSuite {

	public static function suite() {

		$suite = new PHPUnit_Framework_TestSuite( 'PluginsTest' );
		// TODO: Искать папки plugins/*/tests и добавлять тесты сюда
		// $suite->addTestSuite( 't_Plugin' );
		return $suite;
	}
}
