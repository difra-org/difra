<?php /** @noinspection PhpUndefinedClassInspection */

namespace Controller;

class Index extends \Difra\Controller
{
    public function indexAction()
    {
        $this->root->appendChild($this->xml->createElement('index'));
    }
}
