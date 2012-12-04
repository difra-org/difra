<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsIndexController extends Difra\Controller {

    public function dispatch() {

        $this->view->instance = 'adm';
    }

    public function indexAction( ) {

        $indexNode = $this->root->appendChild( $this->xml->createElement( 'announcementsLast' ) );
        $eventsNode = $indexNode->appendChild( $this->xml->createElement( 'announcements' ) );
        \Difra\Plugins\Announcements::getInstance()->getAllEventsXML( $eventsNode );

    }

    public function addAction() {

        $addNode = $this->root->appendChild( $this->xml->createElement( 'announcementsAdd' ) );

        $additionalsFieldsNode = $addNode->appendChild( $this->xml->createElement( 'additionalsFields' ) );
        \Difra\Plugins\Announcements\Additionals::getListXML( $additionalsFieldsNode );

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
            \Difra\Plugins\Blogs\Group::getNewGroupsXml( $addNode, 0, false );
        }
    }

    public function settingsAction() {

        $settingsNode = $this->root->appendChild( $this->xml->createElement( 'announcementsSettings' ) );
        \Difra\Plugins\Announcements::getInstance()->getSettingsXml( $settingsNode );
    }

    public function savesettingsAjaxAction( \Difra\Param\AjaxInt $maxPerUser,
                                            \Difra\Param\AjaxInt $maxPerGroup, \Difra\Param\AjaxInt $width,
                                            \Difra\Param\AjaxInt $height ) {

        $settingsArray = array( 'maxPerUser' => $maxPerUser->val(), 'maxPerGroup' => $maxPerGroup->val(),
                                'width' => $width->val(), 'height' => $height->val() );

        \Difra\Plugins\Announcements::getInstance()->saveSettings( $settingsArray );
        $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/settingsSaved' ) );
    }

    public function saveAjaxAction( \Difra\Param\AjaxFile $eventImage, \Difra\Param\AjaxString $title,
                                    \Difra\Param\AjaxString $eventDate, \Difra\Param\AjaxString $beginDate,
                                    \Difra\Param\AjaxInt $priorityValue, \Difra\Param\AjaxCheckbox $visible,
                                    \Difra\Param\AjaxSafeHTML $shortDescription,

                                    \Difra\Param\AjaxSafeHTML $description = null, \Difra\Param\AjaxInt $group = null,
                                    \Difra\Param\AjaxString $endDate = null ) {

        $data = array( 'title' => $title->val(), 'eventDate' => $eventDate->val(), 'beginDate' => $beginDate->val(),
                        'priority' => $priorityValue->val(), 'visible' => $visible->val(), 'shortDescription' => $shortDescription->val() );

        $data['description'] = is_null( $description ) ? null : $description->val();
        $data['group'] = is_null( $group ) ? null : $group->val();
        $data['endDate'] = is_null( $endDate ) ? null : $endDate->val();
        // из админки пока ставим так, потом добавим выбор юзера.
        $data['user'] = 1;

        $Announcements = \Difra\Plugins\Announcements::getInstance();

        // создаём анонс
        $eventId = $Announcements->create( $data );

        if( is_null( $eventId ) ) {
            return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/createError' ) );
        }

        // записываем картиночку

        $Announcements->saveImage( $eventId, $eventImage->val() );

        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/goodCreate' ) );
        $this->ajax->redirect( '/adm/announcements/' );
    }

    public function savepriorityAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AnyInt $priority ) {

        \Difra\Plugins\Announcements::setPriority( $id->val(), $priority->val() );
        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/prioritySet' ) );
        $this->ajax->refresh();
    }

    public function deleteAction( \Difra\Param\AnyInt $id ) {

        \Difra\Plugins\Announcements::getInstance()->delete( $id->val() );
        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/deleted' ) );
        $this->view->redirect( '/adm/announcements/' );
    }

    public function editAction( \Difra\Param\AnyInt $id ) {

        $editNode = $this->root->appendChild( $this->xml->createElement( 'announcementsEdit' ) );
        \Difra\Plugins\Announcements::getInstance()->getByIdXML( $id->val(), $editNode );

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
            \Difra\Plugins\Blogs\Group::getNewGroupsXml( $editNode, 0, false );
        }
    }

    public function updateAjaxAction( \Difra\Param\AjaxString $title, \Difra\Param\AjaxString $eventDate,
                                      \Difra\Param\AjaxString $beginDate, \Difra\Param\AjaxInt $priorityValue,
                                      \Difra\Param\AjaxCheckbox $visible, \Difra\Param\AjaxSafeHTML $shortDescription,
                                      \Difra\Param\AjaxInt $id,

                                      \Difra\Param\AjaxSafeHTML $description = null, \Difra\Param\AjaxInt $group = null,
                                      \Difra\Param\AjaxString $endDate = null, \Difra\Param\AjaxFile $eventImage = null ) {

        $data = array( 'title' => $title->val(), 'eventDate' => $eventDate->val(), 'beginDate' => $beginDate->val(), 'id' => $id->val(), 
                        'priority' => $priorityValue->val(), 'visible' => $visible->val(), 'shortDescription' => $shortDescription->val()
        );

        $data['description'] = is_null( $description ) ? null : $description->val();
        $data['group'] = is_null( $group ) ? null : $group->val();
        $data['endDate'] = is_null( $endDate ) ? null : $endDate->val();

        // из админки пока ставим так, потом добавим выбор юзера.
        $data['user'] = 1;

        $Announcements = \Difra\Plugins\Announcements::getInstance();

        // апдейтим анонс
        $eventId = $Announcements->create( $data );

        if( is_null( $eventId ) ) {
            return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/updateError' ) );
        }

        if( !is_null( $eventImage ) ) {
            $Announcements->saveImage( $eventId, $eventImage->val() );
        }

        \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/goodUpdate' ) );
        $this->ajax->redirect( '/adm/announcements/' );
    }
}