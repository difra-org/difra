<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \Difra\Exception
	 */
	public function getUri_Fail() {

		$action = new \Difra\Action;
		$action->getUri();
	}
}