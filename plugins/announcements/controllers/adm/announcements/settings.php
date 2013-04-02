<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsSettingsController extends Difra\Controller {

    public function dispatch() {

        $this->view->instance = 'adm';
    }

    public function indexAction() {

        $settingsNode = $this->root->appendChild( $this->xml->createElement( 'announcementsSettings' ) );
        \Difra\Plugins\Announcements::getInstance()->getSettingsXml( $settingsNode );
    }

    public function saveAjaxAction( \Difra\Param\AjaxInt $maxPerUser, \Difra\Param\AjaxInt $maxPerGroup,
                                     \Difra\Param\AjaxInt $width, \Difra\Param\AjaxInt $height,
                                     \Difra\Param\AjaxInt $bigWidth, \Difra\Param\AjaxInt $bigHeight, \Difra\Param\AjaxInt $perPage ) {

        $settingsArray = array(
            'maxPerUser' => $maxPerUser->val(), 'maxPerGroup' => $maxPerGroup->val(), 'perPage' => $perPage->val(),
            'width' => $width->val(), 'height' => $height->val(), 'bigWidth' => $bigWidth->val(), 'bigHeight' => $bigHeight->val()
        );

        \Difra\Plugins\Announcements::getInstance()->saveSettings( $settingsArray );
        $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/settingsSaved' ) );
    }

}