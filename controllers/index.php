<?php

declare(strict_types=1);

namespace Controller;

/**
 * Default / Controller
 */
class Index extends \Difra\Controller
{
    /**
     * Default / action
     */
    public function indexAction()
    {
        $this->root->appendChild($this->xml->createElement('index'));
    }
}
