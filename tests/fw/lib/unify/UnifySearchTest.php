<?php

class UnifySearchTest extends PHPUnit_Framework_TestCase {

	public function test_query_noSuchObjectException() {

		$q = new \Difra\Unify\Search( 'noSuchObject' );
		$this->setExpectedException( 'Difra\Exception' );
		$q->doQuery();
	}
}