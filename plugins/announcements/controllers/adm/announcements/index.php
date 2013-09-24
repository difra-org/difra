<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsIndexController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction( ) {

		$indexNode = $this->root->appendChild( $this->xml->createElement( 'announcementsLast' ) );
		$eventsNode = $indexNode->appendChild( $this->xml->createElement( 'announcements' ) );
		\Difra\Plugins\Announcements::getInstance()->getAllEventsXML( $eventsNode );

		$categoryNode = $indexNode->appendChild( $this->xml->createElement( 'announceCateroty' ) );
		\Difra\Plugins\Announcements\Category::getList( $categoryNode );

	}

	public function addAction() {

		$addNode = $this->root->appendChild( $this->xml->createElement( 'announcementsAdd' ) );

		$additionalsFieldsNode = $addNode->appendChild( $this->xml->createElement( 'additionalsFields' ) );
		$categoryNode = $addNode->appendChild( $this->xml->createElement( 'announceCateroty' ) );
		\Difra\Plugins\Announcements\Additionals::getListXML( $additionalsFieldsNode );
		\Difra\Plugins\Announcements\Category::getList( $categoryNode );

		$locationsNode = $addNode->appendChild( $this->xml->createElement( 'locations' ) );
		\Difra\Plugins\Announcements::getInstance()->getLocationsXML( $locationsNode );

		if( \Difra\Plugger::isEnabled( 'blogs' ) ) {
			\Difra\Plugins\Blogs\Group::getNewGroupsXml( $addNode, 0, false );
		}
	}

	public function saveAjaxAction( \Difra\Param\AjaxFile $eventImage, \Difra\Param\AjaxString $title,
					\Difra\Param\AjaxString $eventDate, \Difra\Param\AjaxString $beginDate,
					\Difra\Param\AjaxInt $priorityValue, \Difra\Param\AjaxCheckbox $visible,
					\Difra\Param\AjaxHTML $description,
					\Difra\Param\AjaxInt $id = null,
					\Difra\Param\AjaxString $shortDescription = null, \Difra\Param\AjaxInt $group = null,
					\Difra\Param\AjaxString $endDate = null, \Difra\Param\AjaxData $additionalField = null,
					\Difra\Param\AjaxInt $category = null, \Difra\Param\AjaxString $fromEventDate = null,
					\Difra\Param\AjaxString $scheduleName = null, \Difra\Param\AjaxData $scheduleField = null,
					\Difra\Param\AjaxData $scheduleValue = null, \Difra\Param\AjaxInt $location = null ) {

		$data = array( 'title' => $title->val(), 'eventDate' => $eventDate->val(), 'beginDate' => $beginDate->val(),
			       'priority' => $priorityValue->val(), 'visible' => $visible->val() );

		$data['description'] = $description;
		$data['shortDescription'] = is_null( $shortDescription ) ? null : $shortDescription->val();
		$data['group'] = is_null( $group ) ? null : $group->val();
		$data['endDate'] = is_null( $endDate ) ? null : $endDate->val();
		$data['category'] = is_null( $category ) ? null : $category->val();
		$data['fromEventDate'] = is_null( $fromEventDate ) ? null : $fromEventDate->val();
		$data['location'] = is_null( $location ) ? null : $location->val();
		if( is_null( $data['fromEventDate'] ) || $data['fromEventDate'] == '' || $data['fromEventDate'] == 'null' ) {
			$data['fromEventDate'] = $eventDate->val();
		}
		// из админки пока ставим так, потом добавим выбор юзера.
		$data['user'] = 1;

		$Announcements = \Difra\Plugins\Announcements::getInstance();

		// создаём анонс
		$eventId = $Announcements->create( $data );

		if( is_null( $eventId ) ) {
			return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/createError' ) );
		}

		// сохраняем дополнительные поля
		if( !is_null( $additionalField ) ) {
			\Difra\Plugins\Announcements\Additionals::saveData( $eventId, $additionalField->val() );
		}

		// записываем картиночку

		$Announcements->saveImage( $eventId, $eventImage );

		// смотрим есть ли расписание
		if( !is_null( $scheduleField ) && !is_null( $scheduleValue ) ) {
			$Announcements->saveSchedules( $eventId, $scheduleName->val(), $scheduleField->val(), $scheduleValue->val() );
		}

		\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/goodCreate' ) );
		$this->ajax->redirect( '/adm/announcements/' );

	}

	public function savepriorityAjaxAction( \Difra\Param\AnyInt $id, \Difra\Param\AnyInt $priority ) {

		\Difra\Plugins\Announcements::setPriority( $id->val(), $priority->val() );
		\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/prioritySet' ) );
		$this->ajax->refresh();
	}

	public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

		\Difra\Plugins\Announcements::getInstance()->delete( $id->val() );
		$this->ajax->refresh();
	}

	public function editAction( \Difra\Param\AnyInt $id ) {

		$editNode = $this->root->appendChild( $this->xml->createElement( 'announcementsEdit' ) );
		\Difra\Plugins\Announcements::getInstance()->getByIdXML( $id->val(), $editNode );

		$additionalsFieldsNode = $editNode->appendChild( $this->xml->createElement( 'additionalsFields' ) );
		$categoryNode = $editNode->appendChild( $this->xml->createElement( 'announceCateroty' ) );
		\Difra\Plugins\Announcements\Additionals::getListXML( $additionalsFieldsNode );
		\Difra\Plugins\Announcements\Category::getList( $categoryNode );

		$locationsNode = $editNode->appendChild( $this->xml->createElement( 'locations' ) );
		\Difra\Plugins\Announcements::getInstance()->getLocationsXML( $locationsNode );


		if( \Difra\Plugger::isEnabled( 'blogs' ) ) {
			\Difra\Plugins\Blogs\Group::getNewGroupsXml( $editNode, 0, false );
		}
	}

	public function updateAjaxAction( \Difra\Param\AjaxString $title, \Difra\Param\AjaxString $eventDate,
					  \Difra\Param\AjaxString $beginDate, \Difra\Param\AjaxInt $priorityValue,
					  \Difra\Param\AjaxCheckbox $visible,
					  \Difra\Param\AjaxInt $id, \Difra\Param\AjaxHTML $description,

					  \Difra\Param\AjaxInt $group = null, \Difra\Param\AjaxString $shortDescription = null,
					  \Difra\Param\AjaxString $endDate = null, \Difra\Param\AjaxFile $eventImage = null,
					  \Difra\Param\AjaxData $additionalField = null, \Difra\Param\AjaxString $fromEventDate = null,
					  \Difra\Param\AjaxInt $category = null, \Difra\Param\AjaxString $scheduleName = null,
					  \Difra\Param\AjaxData $scheduleField = null, \Difra\Param\AjaxData $scheduleValue = null,
					  \Difra\Param\AjaxInt $location = null ) {

		$data = array( 'title' => $title->val(), 'eventDate' => $eventDate->val(), 'beginDate' => $beginDate->val(), 'id' => $id->val(),
			       'priority' => $priorityValue->val(), 'visible' => $visible->val(), 'description' => $description );

		$data['shortDescription'] = is_null( $shortDescription ) ? null : $shortDescription->val();
		$data['group'] = is_null( $group ) ? null : $group->val();
		$data['endDate'] = is_null( $endDate ) ? null : $endDate->val();
		$data['category'] = is_null( $category ) ? null : $category->val();
		$data['fromEventDate'] = is_null( $fromEventDate ) ? null : $fromEventDate->val();
		$data['location'] = is_null( $location ) ? null : $location->val();
		if( is_null( $data['fromEventDate'] ) || $data['fromEventDate'] == '' || $data['fromEventDate'] == 'null' ) {
			$data['fromEventDate'] = $eventDate->val();
		}

		// из админки пока ставим так, потом добавим выбор юзера.
		$data['user'] = 1;

		$Announcements = \Difra\Plugins\Announcements::getInstance();

		// апдейтим анонс
		$eventId = $Announcements->create( $data );

		if( is_null( $eventId ) ) {
			return $this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/updateError' ) );
		}

		// сохраняем дополнительные поля
		if( ! is_null( $additionalField ) ) {
			\Difra\Plugins\Announcements\Additionals::saveData( $eventId, $additionalField->val() );
		}

		if( !is_null( $eventImage ) ) {
			$Announcements->saveImage( $eventId, $eventImage );
		}

		// смотрим есть ли расписание
		if( !is_null( $scheduleField ) && !is_null( $scheduleValue ) ) {
			$scheduleName = !is_null( $scheduleName ) ? $scheduleName->val() : null;
			$Announcements->saveSchedules( $eventId, $scheduleName, $scheduleField->val(), $scheduleValue->val() );
		}

		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'announcements/adm/notify/goodUpdate' ) );
		$this->ajax->redirect( '/adm/announcements/' );
	}
}