<?php

namespace Difra\Plugins;

/**
 * Class Analytics
 *
 * @package Difra\Plugins\Analytics
 */
class Analytics
{
    public static function addAnalyticsXML()
    {
        if (\Difra\View::$instance == 'adm') {
            return;
        }
        if (!$id = \Difra\Config::getInstance()->getValue('ga', 'id')) {
            return;
        }
        $controller = \Difra\Controller::getInstance();
        /** @var \DOMElement $analyticsNode */
        $analyticsNode = $controller->footer->appendChild($controller->xml->createElement('analytics'));
        $analyticsNode->setAttribute('id', $id);
    }
}