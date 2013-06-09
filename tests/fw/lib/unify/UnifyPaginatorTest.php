<?php

class UnifyPaginatorTest extends PHPUnit_Framework_TestCase {

	public function test_l10t1() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPerpage( 10 );
		$paginator->setTotal( 1 );
		$pages = $paginator->getPages();
		$this->assertEquals( $pages, 1 );
	}

	public function test_l10t10() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPerpage( 10 );
		$paginator->setTotal( 10 );
		$pages = $paginator->getPages();
		$this->assertEquals( $pages, 1 );
	}

	public function test_l10t11() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPerpage( 10 );
		$paginator->setTotal( 11 );
		$pages = $paginator->getPages();
		$this->assertEquals( $pages, 2 );
	}

	public function test_limit1() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPerpage( 20 );
		$paginator->setPage( 1 );
		$lim = $paginator->getLimit();
		$this->assertEquals( $lim, array( 0, 20 ) );
	}

	public function test_limit2() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPerpage( 10 );
		$paginator->setPage( 2 );
		$lim = $paginator->getLimit();
		$this->assertEquals( $lim, array( 10, 10 ) );
	}

	public function test_setPage_Fail() {

		$paginator = new \Difra\Unify\Paginator;
		$this->setExpectedException( 'Difra\Exception' );
		$paginator->setPage( 'test' );
	}

	public function test_getXML() {

		$paginator = new \Difra\Unify\Paginator;
		$paginator->setPage( 3 );
		$paginator->setPerpage( 20 );
		$paginator->setTotal( 100 );
		$paginator->setLinkPrefix( '/tests' );
		$paginator->setGet( false );
		$xml1 = new DOMDocument();
		$root1 = $xml1->appendChild( $xml1->createElement( 'test' ) );
		$node = $root1->appendChild( $xml1->createElement( 'paginator' ) );
		$node->setAttribute( 'current', 3 );
		$node->setAttribute( 'pages', 5 );
		$node->setAttribute( 'link', '/tests' );
		$node->setAttribute( 'get', '' );
		$xml2 = new DOMDocument();
		$root2 = $xml2->appendChild( $xml2->createElement( 'test' ) );
		$paginator->getXML( $root2 );
		$this->assertEquals( $xml1, $xml2 );
	}
}