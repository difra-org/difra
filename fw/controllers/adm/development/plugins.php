<?php

/**
 * Class AdmDevelopmentPluginsController
 */
class AdmDevelopmentPluginsController extends \Difra\Controller
{
    public function dispatch()
    {
        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {
        $pluginsNode = $this->root->appendChild($this->xml->createElement('plugins'));
        \Difra\Plugger::getPluginsXML($pluginsNode);
    }

    /**
     * Enable plugin
     *
     * @param \Difra\Param\AnyString $name
     */
    public function enableAjaxAction(\Difra\Param\AnyString $name)
    {
        if (!\Difra\Plugger::turnOn($name->val())) {
            \Difra\Ajaxer::notify(\Difra\Locales::get('adm/plugins/failed'));
        }
        \Difra\Ajaxer::refresh();
    }

    /**
     * Disable plugin
     *
     * @param \Difra\Param\AnyString $name
     */
    public function disableAjaxAction(\Difra\Param\AnyString $name)
    {
        if (!\Difra\Plugger::turnOff($name->val())) {
            \Difra\Ajaxer::notify(\Difra\Locales::get('adm/plugins/failed'));
        }
        \Difra\Ajaxer::refresh();
    }
}
