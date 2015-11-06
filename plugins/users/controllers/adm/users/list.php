<?php

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Plugins, Difra\Param;

/**
 * Class AdmUsersListController
 */
class AdmUsersListController extends Difra\Controller\Adm
{
    /** @var \DOMElement */
    private $node = null;

    /**
     * Users list
     * @param Param\NamedPaginator $page
     */
    public function indexAction(\Difra\Param\NamedPaginator $page = null)
    {
        $this->node = $this->root->appendChild($this->xml->createElement('userList'));
        Plugins\Users\User::getListXML($this->node, $page ? $page->val() : null);
    }

    public function editAction(Param\AnyInt $id)
    {
        $this->node = $this->root->appendChild($this->xml->createElement('userEdit'));
        $user = Plugins\Users\User::getById($id->val());
        if (!$user) {
            return;
        }
        $user->getXML($this->node, false);
//        getUserXML($this->node, $id->val());
    }

    public function saveAjaxAction(
        Param\AnyInt $id,
        Param\AjaxEmail $email,
        Param\AjaxCheckbox $change_pw,
        Param\AjaxString $new_pw = null,
        Param\AjaxData $fieldName = null,
        Param\AjaxData $fieldValue = null
    ) {
        $userData = ['email' => $email->val(), 'change_pw' => $change_pw->val()];
        $userData['new_pw'] = !is_null($new_pw) ? $new_pw->val() : null;

        $userData['addonFields'] = !is_null($fieldName) ? $fieldName->val() : null;
        $userData['addonValues'] = !is_null($fieldValue) ? $fieldValue->val() : null;

        Plugins\Users::getInstance()->setUserLogin($id->val(), $userData);

        if ($userData['change_pw'] != 0 && !is_null($userData['new_pw'])) {
            Ajaxer::notify(Locales::get('auth/adm/userDataSavedPassChanged'));
        } else {
            Ajaxer::notify(Locales::get('auth/adm/userDataSaved'));
        }
        Ajaxer::refresh();
    }

    public function banAjaxAction(Param\AnyInt $id)
    {
        \Difra\Plugins\Users::getInstance()->ban($id->val());
        Ajaxer::refresh();
    }

    public function unbanAjaxAction(Param\AnyInt $id)
    {
        \Difra\Plugins\Users::getInstance()->unban($id->val());
        Ajaxer::refresh();
    }

    public function moderatorAjaxAction(Param\AnyInt $id)
    {
        \Difra\Plugins\Users::getInstance()->setModerator($id->val());
        Ajaxer::refresh();
    }

    public function unmoderatorAjaxAction(Param\AnyInt $id)
    {
        \Difra\Plugins\Users::getInstance()->unSetModerator($id->val());
        Ajaxer::refresh();
    }

    public function activateAjaxAction(Param\AnyInt $id)
    {
        Plugins\Users::getInstance()->manualActivation($id->val());
        Ajaxer::refresh();
    }
}

