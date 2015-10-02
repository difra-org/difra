<?php

namespace Difra\Controller;

use Difra\Config;
use Difra\Exception;

trait Layout
{
    /** @var \DOMDocument */
    public $xml;
    /** @var \DOMElement */
    public $realRoot;

    /** @var \DOMElement Root */
    public $root = null;
    /** @var \DOMElement */
    public $header = null;
    /** @var \DOMElement */
    public $footer = null;
    /** @var \DOMElement[] */
    public $elements = [];

    private function layoutInit()
    {
        // create output XML
        $this->xml = new \DOMDocument;
        $this->realRoot = $this->xml->appendChild($this->xml->createElement('root'));

        // generate page layout
        $layout = Config::getInstance()->get('layout') ?: ['content', 'header', 'footer'];
        if (!in_array('content', $layout)) {
            throw new Exception('Layout has no \'content\' element');
        }
        foreach ($layout as $element) {
            $this->elements[$element] = $this->realRoot->appendChild($this->xml->createElement($element));
        }
        // fill root, header, footer properties
        $this->root =& $this->elements['content'];
        foreach (['header', 'footer'] as $element) {
            if (isset($this->elements['header'])) {
                $this->{$element} =& $this->elements[$element];
            } else {
                $this->{$element} =& $this->root;
            }
        }
    }
}