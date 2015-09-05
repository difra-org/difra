<?php

use Difra\Plugins, Difra\Param;

class NewsController extends Difra\Controller
{
    public function indexAction()
    {

        if (isset($this->action->parameters[0]) && $this->action->parameters[0] != 'page') {
            $this->_viewPublication($this->action->parameters[0]);
            array_shift($this->action->parameters);
            return;
        }

        $page = 1;

        if (isset($this->action->parameters[1]) && $this->action->parameters[1] != '') {
            $page = intval($this->action->parameters[1]);
            array_shift($this->action->parameters);
            array_shift($this->action->parameters);
        }

        $newsNode = $this->root->appendChild($this->xml->createElement('news-list'));
        \Difra\Plugins\News::getInstance()->getListXML($newsNode, $page, false, true);
    }

    private function _viewPublication($link)
    {

        $newsNode = $this->root->appendChild($this->xml->createElement('publication-view'));

        if (!\Difra\Plugins\News::getInstance()->getByLinkXML($link, $newsNode)) {
            $this->view->httpError(404);
        }
    }
}
