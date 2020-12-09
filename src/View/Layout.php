<?php

namespace Difra\View;

use Difra\Config;
use Difra\Exception;

/**
 * Class Layout
 * @package Difra\View
 */
class Layout
{
    /** @var \DOMDocument */
    private $xml;
    /** @var \DOMElement */
    private $realRoot;

    /** @var \DOMElement[] */
    private $elements = [];

    /** @var \Difra\View\HTML\Element\HTML */
    private $html = null;

    /**
     * Singleton
     * @return Layout
     */
    public static function getInstance()
    {
        static $instance = null;
        return $instance ?: $instance = new self;
    }

    /**
     * Constructor
     * @throws Exception
     */
    private function __construct()
    {
        // create output XML
        $this->xml = new \DOMDocument;
        $this->realRoot = $this->xml->appendChild($this->xml->createElement('root'));

        $this->html = new \Difra\View\HTML\Element\HTML();

        // generate page layout
        $layout = Config::getInstance()->get('layout') ?: ['content', 'header', 'footer'];
        if (!in_array('content', $layout)) {
            throw new Exception('Layout has no \'content\' element');
        }
        foreach ($layout as $element) {
            $this->elements[$element] = $this->realRoot->appendChild($this->xml->createElement($element));
        }
    }

    /**
     * Prevent cloning
     */
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
        $controller->html =& $this->html;
        foreach (['header', 'footer'] as $element) {
            if (isset($this->elements['header'])) {
                $controller->{$element} =& $this->elements[$element];
            } else {
                $controller->{$element} =& $this->elements['content'];
            }
        }
    }

    /**
     * Get layout element XML node
     * @param $name
     * @return \DOMElement
     */
    public static function &getElement($name)
    {
        if ($name == 'root') {
            return self::getInstance()->realRoot;
        }
        $me = self::getInstance();
        if (!isset($me->elements[$name])) {
            $me->elements[$name] = $me->realRoot->appendChild($me->xml->createElement($name));
        }
        return $me->elements[$name];
    }

    /**
     * Get all layout elements
     * @return \DOMElement[]
     */
    public static function &getAll()
    {
        return self::getInstance()->elements;
    }
}
