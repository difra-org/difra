<?php

use Difra\Plugins\CDN;

class AdmCdnController extends Difra\Controller
{
	public function dispatch()
	{

		$this->view->instance = 'adm';
	}

	public function hostsAction()
	{

		$hostsXml = $this->root->appendChild($this->xml->createElement('cdn_hosts'));

		\Difra\Plugins\CDN::getInstance()->getHostsXML($hostsXml, true);
	}

	public function deleteAction(\Difra\Param\AnyInt $id)
	{

		\Difra\Plugins\CDN::getInstance()->delete($id->val());
		$this->view->redirect('/adm/cdn/hosts');
	}

	public function checkAjaxAction(\Difra\Param\AnyInt $id)
	{

		// данные хоста
		$CDN = \Difra\Plugins\CDN::getInstance();
		$Locales = \Difra\Locales::getInstance();
		$hostData = $CDN->getHost($id->val());
		if (empty($hostData)) {
			$this->ajax->notify($Locales->getXPath('cdn/adm/notify/noHost'));
			return;
		}

		$result = $CDN->checkHost($hostData['host'], $hostData['port']);

		$this->ajax->display('<span class="cdn-status ' . $result . '"/>' .
							 '<span class="cdn-notify">' . $Locales->getXPath('cdn/adm/legend/' . $result) . '</span>' .
							 '<div><a href="#" class="button" onclick="window.location.reload();">'
							 . $Locales->getXPath('cdn/adm/close') . '</a></div>');
	}

	public function addhostAction()
	{

		$this->root->appendChild($this->xml->createElement('cdn_add_host'));
	}

	public function addhostAjaxAction(\Difra\Param\AjaxString $host, \Difra\Param\AjaxInt $port)
	{

		$res = \Difra\Plugins\CDN::getInstance()->addHost($host->val(), $port->val());
		if ($res) {
			$this->ajax->display(\Difra\Locales::getInstance()->getXPath('cdn/adm/notify/added') .
								 '<div><a href="#" class="button" onclick="window.location = \'/adm/cdn/hosts\';">' .
								 \Difra\Locales::getInstance()->getXPath('cdn/adm/close') . '</a></div>');
		} else {
			$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('cdn/adm/notify/duplicated'));
		}
	}

	public function editAction(\Difra\Param\AnyInt $id)
	{

		$editXml = $this->root->appendChild($this->xml->createElement('cdn_edit_host'));
		\Difra\Plugins\CDN::getInstance()->getHostXML($editXml, $id->val());
	}

	public function editAjaxAction(\Difra\Param\AjaxString $host, \Difra\Param\AjaxInt $port, \Difra\Param\AnyInt $id)
	{

		\Difra\Plugins\CDN::getInstance()->saveHost($id->val(), $host->val(), $port->val());
		$this->ajax->display(\Difra\Locales::getInstance()->getXPath('cdn/adm/notify/saved')
							 . '<div><a href="#" class="button" onclick="window.location = \'/adm/cdn/hosts\';">' .
							 \Difra\Locales::getInstance()->getXPath('cdn/adm/close') . '</a></div>');
	}

	public function settingsAction()
	{

		$settingsNode = $this->root->appendChild($this->xml->createElement('cdn_settings'));
		\Difra\Plugins\CDN::getInstance()->getSettingsXML($settingsNode);
	}

	public function savesettingsAjaxAction(
		\Difra\Param\AjaxInt $timeout,
		\Difra\Param\AjaxInt $cachetime,
		\Difra\Param\AjaxInt $failtime,
		\Difra\Param\AjaxInt $selecttime
	) {

		\Difra\Plugins\CDN::getInstance()->saveSettings([
			'timeout' => $timeout->val(),
			'cachetime' => $cachetime->val(),
			'failtime' => $failtime->val(),
			'selecttime' => $selecttime->val()
		]);
		$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('cdn/adm/notify/settingSaved'));
	}
}

