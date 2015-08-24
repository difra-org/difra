<?php

/**
 * CMS plugin adds hook for pre-action event. If hook detects CMS page is requested, Action is configured to call this
 * controller.
 */

namespace Difra\Plugins\CMS;

/**
 * Class Controller
 *
 * @package Difra\Plugins\CMS
 */
class Controller extends \Difra\Controller
{
    /**
     * @param \Difra\Param\AnyInt $id
     */
    public function pageAction(\Difra\Param\AnyInt $id)
    {
        /** @var $pageNode \DOMElement */
        $pageNode = $this->root->appendChild($this->xml->createElement('page'));
        $page = Page::get($id->val());
        $page->getXML($pageNode);
        $this->root->setAttribute('pageTitle', $page->getTitle());
    }
}