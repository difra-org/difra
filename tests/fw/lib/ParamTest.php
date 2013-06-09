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

	public function test_Email() {

		$this->assertFalse( \Difra\Param\AjaxEmail::verify( 0 ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( null ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( array() ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( \Difra\Action::getInstance() ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( 'user@jam' ) );
		$this->assertTrue( \Difra\Param\AjaxEmail::verify( 'user@mail.jam' ) );
		$this->assertTrue( \Difra\Param\AjaxEmail::verify( 'user@a-jam.ru' ) );
		$this->assertTrue( \Difra\Param\AjaxEmail::verify( 'the.user@a-jam.ru' ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( 'us.@difra.ru' ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( '.us@difra.ru' ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( 'us..er@difra.ru' ) );
		$this->assertTrue( \Difra\Param\AjaxEmail::verify( 'u.s.er@difra.ru' ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( '@a-jam.ru' ) );
		$this->assertFalse( \Difra\Param\AjaxEmail::verify( 'user@' ) );

		$i = new \Difra\Param\AjaxEmail( 'user@difra.ru' );
		$this->assertEquals( $i->val(), 'user@difra.ru' );
		$this->assertEquals( $i->raw(), 'user@difra.ru' );
		$this->assertEquals( (string)$i, 'user@difra.ru' );
	}

	public function test_CheckBox() {

		$cb1 = new \Difra\Param\AjaxCheckbox( 'on' );
		$this->assertTrue( $cb1->val() );
		$this->assertEquals( (string)$cb1, '1' );
		$cb2 = new \Difra\Param\AjaxCheckbox();
		$this->assertFalse( $cb2->val() );
		$this->assertEquals( (string)$cb2, '' );

		$this->assertEquals( \Difra\Param\AjaxCheckbox::getSource(), 'ajax' );
		$this->assertTrue( \Difra\Param\AjaxCheckbox::isNamed() );
		$this->assertTrue( \Difra\Param\AjaxCheckbox::isAuto() );
	}

	public function test_Files() {

		$file1 = array(
			'error' => UPLOAD_ERR_OK,
			'tmp_name' => __DIR__ . '/data/file1.txt'
		);
		$file2 = array(
			'error' => UPLOAD_ERR_OK,
			'tmp_name' => __DIR__ . '/data/file2.txt'
		);
		$file3 = array(
			'error' => UPLOAD_ERR_INI_SIZE
		);

		$this->assertFalse( \Difra\Param\AjaxFile::verify( null ) );
		$this->assertTrue( \Difra\Param\AjaxFile::verify( $file1 ) );
		$this->assertTrue( \Difra\Param\AjaxFile::verify( $file2 ) );
		$this->assertFalse( \Difra\Param\AjaxFile::verify( $file3 ) );

		$this->assertFalse( \Difra\Param\AjaxFiles::verify( null ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file1 ) ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file2 ) ) );
		$this->assertFalse( \Difra\Param\AjaxFiles::verify( array( $file3 ) ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file1, $file2 ) ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file1, $file3 ) ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file2, $file3 ) ) );
		$this->assertTrue( \Difra\Param\AjaxFiles::verify( array( $file1, $file2, $file3 ) ) );

		$files = new \Difra\Param\AjaxFiles( array( $file1, $file2, $file3 ) );
		$this->assertEquals( $files->val(), array( file_get_contents( __DIR__ . '/data/file1.txt' ), file_get_contents( __DIR__ . '/data/file2.txt' ) ) );

		$file = new \Difra\Param\AjaxFile( $file3 );
		$this->assertNull( $file->val() );
		$this->assertEquals( $file->raw(), $file3 );
	}
}
