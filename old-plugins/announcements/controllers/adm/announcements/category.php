<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsCategoryController extends Difra\Controller
{
    public function dispatch()
    {

        \Difra\View::$instance = 'adm';
    }

    public function indexAction()
    {

        $categoryNode = $this->root->appendChild($this->xml->createElement('announcementsCategory'));
        \Difra\Plugins\Announcements\Category::getList($categoryNode);
    }

    public function saveAjaxAction(
        \Difra\Param\AjaxString $categoryName,
        \Difra\Param\AjaxString $categoryAlias,
        \Difra\Param\AjaxInt $catId = null,
        \Difra\Param\AjaxString $originalAlias = null
    ) {

        $catId = !is_null($catId) ? $catId->val() : null;

        $Announcements = \Difra\Plugins\Announcements::getInstance();

        if (is_null($catId) || $originalAlias->val() != $categoryAlias->val()) {

            if (\Difra\Plugins\Announcements\Category::checkName($categoryAlias->val())) {
                $this->ajax->invalid('categoryAlias',
                    \Difra\Locales::getInstance()->getXPath('announcements/adm/category/duplicateName'));
                return;
            }
        }

        $Announcements->saveCategory($categoryAlias->val(), $categoryName->val(), $catId);

        if (is_null($catId)) {
            $this->ajax->notify(\Difra\Locales::getInstance()->getXPath('announcements/adm/category/added'));
        } else {
            $this->ajax->notify(\Difra\Locales::getInstance()->getXPath('announcements/adm/category/updated'));
        }
        $this->ajax->refresh();
    }

    public function deleteAjaxAction(\Difra\Param\AnyInt $id)
    {

        \Difra\Plugins\Announcements\Category::delete($id->val());
        $this->ajax->refresh();
    }
}
