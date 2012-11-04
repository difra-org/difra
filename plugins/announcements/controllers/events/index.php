<?php

use Difra\Param, Difra\Plugins\Announcements;

class EventsIndexController extends \Difra\Controller {

    private $eventRoot = null;

    public function indexAction( \Difra\Param\AnyString $link = null ) {

        if( !is_null( $link ) ) {
            // страница анонса события

            $this->eventRoot = $this->root->appendChild( $this->xml->createElement( 'event' ) );
            $this->_showEvent( rawurldecode( $link->val() ) );
        } else {

            $Group = \Difra\Plugins\Blogs\Group::current();
            if( $Group ) {
                // страница анонсов группы

                $this->eventRoot = $this->root->appendChild( $this->xml->createElement( 'groupEvents' ) );
                $groupId = $Group->getId();
                $this->_showGroupEvents( $groupId );

            } else {
                // общая страница анонсов

                $this->eventRoot = $this->root->appendChild( $this->xml->createElement( 'allEvents' ) );
                $this->_showByPriority();
            }
        }

    }

    private function _showEvent( $link ) {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        if( !$Announcements->getByLinkXML( $link, $this->eventRoot ) ) {

            $this->view->httpError( 404 );
        }
    }

    private function _showByPriority( $priority = 100 ) {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        $Announcements->getByPriorityXML( $this->eventRoot, $priority );
    }

    private function _showGroupEvents( $groupId ) {

        $Announcements = \Difra\Plugins\Announcements::getInstance();
        $Announcements->getByGroupXML( $groupId, $this->eventRoot );
    }

}