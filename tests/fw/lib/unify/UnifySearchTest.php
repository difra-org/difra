<?php

class UnifySearchTest extends PHPUnit_Framework_TestCase {

	public function test_query_noSuchObjectException() {

		$this->setExpectedException( 'Difra\Exception' );
		$q = new \Difra\Unify\Search( 'noSuchObject' );
//		$q->doQuery();
	}
}