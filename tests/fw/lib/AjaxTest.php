<?php

class AjaxTest extends PHPUnit_Framework_TestCase {

	/**
	 * @backupGlobals enabled
	 */
	public function test_actions() {

		$ajax = \Difra\Ajax::getInstance();
		$actions = array();

		$ajax->notify( 'notification message' );
		$actions[] = array( 'action' => 'notify',
				    'message' => 'notification message',
				    'lang' => array( 'close' => \Difra\Locales::getInstance()->getXPath( 'notifications/close' ) ) );

		$ajax->display( '<span>test</span>' );
		$actions[] = array( 'action' => 'display',
				    'html' => '<span>test</span>'
		);

		$ajax->error( 'error message <span>test</span>' );
		$actions[] = array(
			'action' => 'error',
			'message' => 'error message &lt;span&gt;test&lt;/span&gt;',
			'lang' => array(
				'close' => \Difra\Locales::getInstance()->getXPath( 'notifications/close' )
			)
		);

		$ajax->required( 'element' );
		$actions[] = array(
			'action' => 'require',
			'name' => 'element'
		);

		$ajax->invalid( 'inv1' );
		$actions[] = array(
			'action' => 'invalid',
			'name' => 'inv1'
		);

		$ajax->invalid( 'inv2', 'invalid value' );
		$actions[] = array(
			'action' => 'invalid',
			'name' => 'inv2',
			'message' => 'invalid value'
		);

		$ajax->status( 'field1', 'bad value', 'problem' );
		$actions[] = array(
			'action' => 'status',
			'name' => 'field1',
			'message' => 'bad value',
			'classname' => 'problem'
		);

		$ajax->redirect( '/some/page' );
		$actions[] = array(
			'action' => 'redirect',
			'url' => '/some/page'
		);

		$_SERVER['HTTP_REFERER'] = '/current/page';
		$ajax->refresh();
		$actions[] = array(
			'action' => 'redirect',
			'url' => '/current/page'
		);

		$ajax->reload();
		$actions[] = array(
			'action' => 'reload'
		);

		$ajax->load( 'someid', 'some <b>content</b>' );
		$actions[] = array(
			'action' => 'load',
			'target' => 'someid',
			'html' => 'some <b>content</b>'
		);

		$ajax->close();
		$actions[] = array(
			'action' => 'close'
		);

		$ajax->reset();
		$actions[] = array(
			'action' => 'reset'
		);

		\Difra\Envi::setUri( '/current/page' );
		$ajax->confirm( 'Are you sure?' );
		$actions[] = array(
			'action' => 'display',
			'html' => '<form action="/current/page" class="ajaxer"><input type="hidden" name="confirm" value="1"/>' .
			'<div>Are you sure?</div>' .
			'<input type="submit" value="' . \Difra\Locales::getInstance()->getXPath( 'ajaxer/confirm-yes' ) . '"/>' .
			'<input type="button" value="' . \Difra\Locales::getInstance()->getXPath( 'ajaxer/confirm-no' ) . '" onclick="ajaxer.close(this)"/>' .
			'</form>'
		);

		$this->assertEquals( $ajax->getResponse(), json_encode( array( 'actions' => $actions ) ) );

		$ajax->clean();
		$this->assertEquals( $ajax->getResponse(), '[]' );
		$this->assertFalse( $ajax->hasProblem() );

		$ajax->reload();
		$ajax->clean( true );
		$this->assertEquals( $ajax->getResponse(), '[]' );
		$this->assertTrue( $ajax->hasProblem() );
	}
}