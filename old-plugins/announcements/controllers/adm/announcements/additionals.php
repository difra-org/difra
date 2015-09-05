<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsAdditionalsController extends Difra\Controller
{
    public function dispatch()
    {

        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {

        $addNode = $this->root->appendChild($this->xml->createElement('announcementsAdditionals'));
        \Difra\Plugins\Announcements\Additionals::getListXML($addNode);
    }

    public function saveAjaxAction(
        \Difra\Param\AjaxString $name,
        \Difra\Param\AjaxString $alias,
        \Difra\Param\AjaxInt $id = null,
        \Difra\Param\AjaxString $originalAlias = null
    ) {

        $id = !is_null($id) ? $id->val() : null;

        if (is_null($id) || $originalAlias->val() != $alias->val()) {
            if (\Difra\Plugins\Announcements\Additionals::checkAlias($alias->val())) {
                \Difra\Ajaxer::getInstance()->invalid('alias',
                    \Difra\Locales::getInstance()->getXPath('announcements/adm/additionals/duplicateName'));
                return;
            }
        }

        \Difra\Plugins\Announcements::getInstance()->saveAdditionalField($name->val(), $alias->val(), $id);

        if (is_null($id)) {
            \Difra\Ajaxer::getInstance()->notify(\Difra\Locales::getInstance()
                                                               ->getXPath('announcements/adm/additionals/added'));
        } else {
            \Difra\Ajaxer::getInstance()->notify(\Difra\Locales::getInstance()
                                                               ->getXPath('announcements/adm/additionals/updated'));
        }
        \Difra\Ajaxer::getInstance()->refresh();
    }

    public function deleteAction(\Difra\Param\AnyInt $id)
    {

        \Difra\Plugins\Announcements\Additionals::delete($id->val());
        \Difra\Ajaxer::getInstance()->refresh();
    }
}
