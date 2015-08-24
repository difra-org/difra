<?php

/**
 * Class IndexController
 * Default index controller.
 */
class IndexController extends \Difra\Controller
{
    public function indexAction()
    {
        $this->root->appendChild($this->xml->createElement('index'));
    }
}