<?php

use Difra\Plugins, Difra\Param;

class TagController extends Difra\Controller
{
    public function indexAjaxActionAuth()
    {

        //$this->view->rendered = true;

        if (isset($_GET['moduleName']) && $_GET['moduleName'] != '' && isset($_GET['query']) && $_GET['query'] != '') {

            $suggestTags = Difra\Plugins\Tags::getInstance()->suggest(
                $_GET['moduleName'], trim($_GET['query']));

            if (!empty($suggestTags)) {
                $this->ajax->setResponse('query', addslashes(htmlspecialchars($_GET['query'])));
                $this->ajax->setResponse('suggestions', array_values($suggestTags));
                return;
            }
        }
        $this->ajax->setResponse('query', '');
        $this->ajax->setResponse('suggestions', '');
    }
}

