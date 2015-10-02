<?php

namespace Difra\View;

use Difra\Config;
use Difra\Exception;

class Layout
{
    /** @var \DOMDocument */
    private $xml;
    /** @var \DOMElement */
    private $realRoot;

    /** @var \DOMElement[] */
    private $elements = [];

    public static function getInstance()
    {
        static $instance = null;
        return $instance ?: $instance = new self;
    }

    private function __construct()
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
    }

    private function __clone()
    {
    }

    /**
     * Link layout elements to controller
     * @param \Difra\Controller $controller
     */
    public function linkController($controller)
    {
        $controller->xml =& $this->xml;
        $controller->realRoot =& $this->realRoot;
        $controller->root =& $this->elements['content'];
        foreach (['header', 'footer'] as $element) {
            if (isset($this->elements['header'])) {
                $controller->{$element} =& $this->elements[$element];
            } else {
                $controller->{$element} =& $this->elements['content'];
            }
        }
    }

    public static function &getElement($name)
    {
        if ($name == 'root') {
            return self::getInstance()->xml;
        }
        $me = self::getInstance();
        if (!isset($me->elements[$name])) {
            $me->elements[$name] = $me->realRoot->appendChild($me->xml->createElement($name));
        }
        return $me->elements[$name];
    }

    public static function &getAll()
    {
        return self::getInstance()->elements;
    }
}