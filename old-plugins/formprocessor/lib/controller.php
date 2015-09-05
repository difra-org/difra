<?php

namespace Difra\Plugins\FormProcessor;

class Controller extends \Difra\Controller
{
    public function formAction(\Difra\Param\AnyInt $id)
    {

        $formViewXml = $this->root->appendChild($this->xml->createElement('fp_viewform'));
        \Difra\Plugins\FormProcessor::getInstance()->getFormXML($formViewXml, $id->val());
    }
}
