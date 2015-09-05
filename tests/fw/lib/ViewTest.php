<?php

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function test_render_dontEcho()
    {
        $xml = new \DOMDocument;
        $realRoot = $xml->appendChild($xml->createElement('root'));
        $realRoot->appendChild($xml->createElement('content'));
        $html = \Difra\View::render($xml, 'main', true);
        $this->assertNotEmpty($html);
    }

    public function test_render_echo()
    {
        $xml = new \DOMDocument;
        $realRoot = $xml->appendChild($xml->createElement('root'));
        $realRoot->appendChild($xml->createElement('content'));
        ob_start();
        \Difra\View::render($xml, 'main');
        $html = ob_get_clean();
        $this->assertNotEmpty($html);
    }
}
