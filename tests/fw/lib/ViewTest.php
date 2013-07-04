<?php

class ViewTest extends PHPUnit_Framework_TestCase {

	public function test_render_dontEcho() {

		$view = new \Difra\View;
		$xml = new \DOMDocument;
		$realRoot = $xml->appendChild( $xml->createElement( 'root' ) );
		$realRoot->appendChild( $xml->createElement( 'content' ) );
		$html = $view->render( $xml, 'main', true );
		$this->assertNotEmpty( $html );
	}

	public function test_render_echo() {

		$view = new \Difra\View;
		$xml = new \DOMDocument;
		$realRoot = $xml->appendChild( $xml->createElement( 'root' ) );
		$realRoot->appendChild( $xml->createElement( 'content' ) );
		ob_start();
		$view->render( $xml, 'main' );
		$html = ob_get_clean();
		$this->assertNotEmpty( $html );
	}

}