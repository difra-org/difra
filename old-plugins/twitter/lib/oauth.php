<?php

namespace Difra\Plugins\Twitter;

use Difra\Config;
use Difra\Exception;

class Oauth
{
	private $accessToken = null;
	private $accessTokenSecret = null;
	private $consumerKey = null;
	private $consumerSecret = null;
	private $postFields = null;
	private $getFields = null;
	private $url = null;
	private $method = 'POST';
	protected $oAuthArray = null;
	private $header = null;

	/**
	 * Создаёт и возвращает объект авторизации для конкретного запроса
	 * @param $requestUrl
	 * @param string $method
	 * @return Oauth
	 * @throws Exception
	 */
	public static function build($requestUrl, $method = 'POST')
	{
		$OAuth = new self;
		$OAuth->_getConfig();
		$OAuth->url = $requestUrl;
		$OAuth->method = $method;

		$requestFields = [
			'oauth_consumer_key' => $OAuth->consumerKey,
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $OAuth->accessToken,
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		];
		$baseInfoString = $OAuth->_getBaseString($requestFields);

		// композиция ключа
		$compositeKey = rawurlencode($OAuth->consumerSecret) . '&' . rawurlencode($OAuth->accessTokenSecret);

		// подпись запроса
		$requestFields['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseInfoString, $compositeKey, true));
		$OAuth->oAuthArray = $requestFields;
		$OAuth->_getRequestHeader();
		return $OAuth;
	}

	/**
	 * Получаем конфиг авторизации OAuth
	 * @throws Exception
	 */
	private function _getConfig()
	{
		$config = Config::getInstance()->get('oAuth');

		if (empty($config)) {
			throw new Exception('No OAuth config.');
		}

		$this->consumerKey = $config['consumerKey'];
		$this->consumerSecret = $config['consumerSecret'];
		$this->accessToken = $config['accessToken'];
		$this->accessTokenSecret = $config['accessTokenSecret'];
	}

	/**
	 * Возвращает базовую строку для дальнейшего использования с cUrl.
	 * @param $oAuthParams
	 * @return string
	 */
	private function _getBaseString($oAuthParams)
	{
		$returnArray = [];
		ksort($oAuthParams);

		foreach ($oAuthParams as $key => $value) {
			$returnArray[] = $key . '=' . $value;
		}

		$returnString = $this->method . '&' . rawurlencode($this->url) . '&' . rawurlencode(implode('&', $returnArray));

		return $returnString;
	}

	/**
	 * Создаёт хедер авторизации
	 */
	private function _getRequestHeader()
	{
		$header = 'Authorization: OAuth ';
		$values = [];
		if (empty($this->oAuthArray)) {
			throw new Exception('The OAuth is not built.');
		}

		foreach ($this->oAuthArray as $key => $value) {
			$values[] = $key . '="' . rawurlencode($value) . '"';
		}
		$this->header = $header . implode(', ', $values);
	}

	/**
	 * Возвращает хедер авторизации для cUrl.
	 * @return array
	 */
	public function getHeader()
	{
		return [$this->header, 'Expect:'];
	}
}
