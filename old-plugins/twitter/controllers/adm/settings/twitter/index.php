<?php

class AdmSettingsTwitterIndexController extends \Difra\Controller
{
	public function dispatch()
	{
		\Difra\View::$instance = 'adm';
	}

	public function indexAction()
	{
		/** @var \DOMElement $mainXml */
		$mainXml = $this->root->appendChild($this->xml->createElement('twitterSettings'));
		$config = \Difra\Config::getInstance()->get('oAuth');
		if (!empty($config)) {
			foreach ($config as $key => $value) {
				$mainXml->setAttribute($key, $value);
			}
		}
	}

	public function savesettingsAjaxAction(
		\Difra\Param\AjaxString $consumerKey,
		\Difra\Param\AjaxString $consumerSecret,
		\Difra\Param\AjaxString $oauthToken,
		\Difra\Param\AjaxString $oauthSecret,
		\Difra\Param\AjaxCheckbox $postToTwitter
	) {

		$oAuthArray = [
			'consumerKey' => $consumerKey->val(),
			'consumerSecret' => $consumerSecret->val(),
			'accessToken' => $oauthToken->val(),
			'accessTokenSecret' => $oauthSecret->val(),
			'postToTwitter' => $postToTwitter->val()
		];

		\Difra\Config::getInstance()->set('oAuth', $oAuthArray);

		\Difra\Ajaxer::refresh();
		\Difra\Ajaxer::notify(\Difra\Locales::getInstance()->getXPath('twitter/adm/oAuth/settingsSaved'));
	}
}
