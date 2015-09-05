<?php

class AdmSettingsRssIndexController extends \Difra\Controller
{
	public function dispatch()
	{

		\Difra\View::$instance = 'adm';
	}

	public function indexAction()
	{

		$setNode = $this->root->appendChild($this->xml->createElement('rss_settings'));
		Difra\Plugins\Rss::getSettingsXML($setNode);
	}

	public function savesettingsAjaxAction(
		\Difra\Param\AjaxCheckbox $onLine,
		\Difra\Param\AjaxString $title,
		\Difra\Param\AjaxString $link,
		\Difra\Param\AjaxInt $ttl,
		\Difra\Param\AjaxInt $size,
		\Difra\Param\AjaxCheckbox $image,
		\Difra\Param\AjaxCheckbox $cache,
		\Difra\Param\AjaxString $desc = null,
		\Difra\Param\AjaxString $copyright = null,
		\Difra\Param\AjaxFile $rsslogo = null
	) {

		$settingsArray = [
			'onLine' => $onLine->val(),
			'title' => $title->val(),
			'link' => $link->val(),
			'ttl' => $ttl->val(),
			'size' => $size->val(),
			'image' => $image->val(),
			'cache' => $cache->val()
		];

		if (!is_null($desc)) {
			$settingsArray['description'] = $desc->val();
		}
		if (!is_null($copyright)) {
			$settingsArray['copyright'] = $copyright->val();
		}
		if (!is_null($rsslogo)) {
			$settingsArray['logo'] = $rsslogo;
		}
		Difra\Plugins\Rss::saveSettings($settingsArray);

		$this->ajax->notify(\Difra\Locales::getInstance()->getXPath('rss/adm/saved'));
		$this->ajax->refresh();
	}

	public function deletelogoAjaxAction()
	{

		Difra\Plugins\Rss::deleteLogo();
		$this->ajax->refresh();
	}
}
