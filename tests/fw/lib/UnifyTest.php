<?php

class UnifyTest extends PHPUnit_Framework_TestCase {

	public function test_get() {

		include_once( __DIR__ . '/data/test-obj.inc' );
		\Difra\Unify::registerObjects( array() );
		\Difra\Unify::registerObjects( array( 'test' => 'TestObj' ) );
		$test = \Difra\Unify::getObj( 'test', 10 ); // проверка кэша
		$test2 = TestObj::get( 10 );
		$this->assertSame( $test, $test2 );
		$this->assertEquals( $test::getTable(), 'test_table' );
		$this->assertEquals( $test::getPrimary(), 'id' );
		$this->assertEquals( $test->getPrimaryValue(), 10 );
		$this->assertEquals( $test->getKeys( false ), array( 'id', 'visible', 'parent', 'test2', 'title' ) );
		$this->assertEquals( $test->getKeys( false ), array( 'id', 'visible', 'parent', 'test2', 'title' ) ); // ещё раз для проверки кэшей
		$this->assertEquals( $test->getKeys( true ), array( 'id', 'visible', 'parent', 'test2', 'title', 'description' ) );
		$this->assertEquals( $test->getKeys( 'only' ), array( 'description' ) );
		$this->assertEquals( $test->getDefaultSearchConditions(), array( 'visible' => 1 ) );
		$this->setExpectedException( 'Difra\Exception' );
		/** @noinspection PhpUndefinedFieldInspection */
		$test2->noSuchField = '1';
	}

	public function test_UnknownObject() {

		$this->setExpectedException( 'Difra\Exception' );
		\Difra\Unify::getObj( 'noSuchObject123', 123 );
	}

}