<?php

use Difra\Plugins, Difra\Plugins\Announcements, Difra\Param;

class AdmAnnouncementsLocationsController extends Difra\Controller
{
	public function dispatch()
	{

		\Difra\View::$instance = 'adm';
	}

	public function indexAction()
	{

		$LocNode = $this->root->appendChild($this->xml->createElement('announcementsLocations'));
		\Difra\Plugins\Announcements::getInstance()->getLocationsXML($LocNode);
	}

	public function saveAction(
		\Difra\Param\AjaxString $name,
		\Difra\Param\AjaxString $url = null,
		\Difra\Param\AjaxString $address = null,
		\Difra\Param\AjaxString $phone = null,
		\Difra\Param\AjaxString $info = null,
		\Difra\Param\AjaxInt $id = null
	) {

		$url = !is_null($url) ? $url->val() : null;
		$address = !is_null($address) ? $address->val() : null;
		$phone = !is_null($phone) ? $phone->val() : null;
		$info = !is_null($info) ? $info->val() : null;
		$dataArray = ['name' => $name->val(), 'url' => $url, 'address' => $address, 'phone' => $phone, 'info' => $info];
		$id = !is_null($id) ? $id->val() : null;
		\Difra\Plugins\Announcements::getInstance()->saveLocation($dataArray, $id);

		if (!is_null($id)) {
			$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('announcements/adm/locations/updated'));
			$this->ajax->redirect('/adm/announcements/locations/');
		} else {
			$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('announcements/adm/locations/added'));
			$this->ajax->refresh();
		}
	}

	public function deleteAjaxAction(\Difra\Param\AnyInt $id)
	{

		\Difra\Plugins\Announcements::getInstance()->deleteLocation($id->val());
		$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('announcements/adm/locations/deleted'));
		$this->ajax->refresh();
	}

	public function editAction(\Difra\Param\AnyInt $id)
	{

		$LocNode = $this->root->appendChild($this->xml->createElement('announcementsLocationsEdit'));
		$LocNode->setAttribute('id', $id->val());
		\Difra\Plugins\Announcements::getInstance()->getLocationByIdXML($id->val(), $LocNode);
	}
}
