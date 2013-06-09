<?php

class ParamTest extends PHPUnit_Framework_TestCase {

	public function test_Int() {

		$this->assertTrue( \Difra\Param\AjaxInt::verify( 10 ) );
		$this->assertTrue( \Difra\Param\AjaxInt::verify( 0 ) );
		$this->assertTrue( \Difra\Param\AjaxInt::verify( '0' ) );
		$this->assertTrue( \Difra\Param\AjaxInt::verify( '10' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( '10a' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( '0x00' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( '0xff' ) );
		$this->assertTrue( \Difra\Param\AjaxInt::verify( -4 ) );
		$this->assertTrue( \Difra\Param\AjaxInt::verify( '-4' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( 'a' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( null ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( 3.8 ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( -4.6 ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( '3.8' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( '-4.6' ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( array( 'abc' ) ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( array( 10 ) ) );
		$this->assertFalse( \Difra\Param\AjaxInt::verify( \Difra\Action::getInstance() ) );

		$i = new \Difra\Param\AjaxInt( '-10' );
		$this->assertEquals( $i->val(), -10 );
		$this->assertEquals( $i->raw(), -10 );
		$this->assertEquals( (string)$i, '-10' );
	}

	public function test_String() {

		$this->assertTrue( \Difra\Param\AjaxString::verify( 10 ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( 0 ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '0' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '10' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '10a' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '0x00' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '0xff' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( -4 ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '-4' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( 'a' ) );
		$this->assertFalse( \Difra\Param\AjaxString::verify( null ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( 3.8 ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( -4.6 ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '3.8' ) );
		$this->assertTrue( \Difra\Param\AjaxString::verify( '-4.6' ) );
		$this->assertFalse( \Difra\Param\AjaxString::verify( array( 'abc' ) ) );
		$this->assertFalse( \Difra\Param\AjaxString::verify( array( 10 ) ) );
		$this->assertFalse( \Difra\Param\AjaxString::verify( \Difra\Action::getInstance() ) );

		$i = new \Difra\Param\AjaxString( '-10a' );
		$this->assertEquals( $i->val(), '-10a' );
		$this->assertEquals( $i->raw(), '-10a' );
		$this->assertEquals( (string)$i, '-10a' );
	}

	public function test_Float() {

		$this->assertTrue( \Difra\Param\AjaxFloat::verify( 10 ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( 0 ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '0' ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '10' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( '10a' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( '0x00' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( '0xff' ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( -4 ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '-4' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( 'a' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( null ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( 3.8 ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( -4.6 ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '3.8' ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '-4.6' ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '3,8' ) );
		$this->assertTrue( \Difra\Param\AjaxFloat::verify( '-4,6' ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( array( 'abc' ) ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( array( 10 ) ) );
		$this->assertFalse( \Difra\Param\AjaxFloat::verify( \Difra\Action::getInstance() ) );

		$i = new \Difra\Param\AjaxFloat( -10.3 );
		$this->assertEquals( $i->val(), -10.3 );
		$this->assertEquals( $i->raw(), -10.3 );
		$this->assertEquals( (string)$i, '-10.3' );
	}

	public function test_Data() {

		$this->assertTrue( \Difra\Param\AjaxData::verify( 10 ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( 0 ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '0' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '10' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '10a' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '0x00' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '0xff' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( -4 ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '-4' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( 'a' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( null ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( 3.8 ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( -4.6 ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '3.8' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '-4.6' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '3,8' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( '-4,6' ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( array( 'abc' ) ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( array( 10 ) ) );
		$this->assertTrue( \Difra\Param\AjaxData::verify( \Difra\Action::getInstance() ) );

		$i = new \Difra\Param\AjaxData( array( '1', 2, 3 ) );
		$this->assertEquals( $i->val(), array( '1', 2, 3 ) );
		$this->assertEquals( $i->raw(), array( '1', 2, 3 ) );
		$this->assertEquals( (string)$i, '' );
	}
}
