<?php

/**
 * Class AdmDevelopmentConfigController
 */
class AdmDevelopmentConfigController extends \Difra\Controller
{
    public function dispatch()
    {
        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {
        if (!\Difra\Debugger::isEnabled()) {
            throw new \Difra\View\Exception(404);
        }
        $config = \Difra\Config::getInstance();
        /** @var \DOMElement $configNode */
        $configNode = $this->root->appendChild($this->xml->createElement('configuration'));
        $conf = $config->getConfig();
        $configNode->setAttribute('current', var_export($conf, true));
        $configNode->setAttribute('diff', var_export($config->getDiff(), true));
    }

    public function resetAjaxAction()
    {
        \Difra\Config::getInstance()->reset();
        \Difra\Ajaxer::notify(\Difra\Locales::get('adm/config/reset-done'));
        \Difra\Ajaxer::refresh();
    }
}