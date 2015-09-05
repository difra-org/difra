<?php

namespace Difra\Plugins\SAPE;

abstract class Common
{
	const SAPE_VERSION = '1.1.10';
	const SAPE_TTL = 3600;
	const SAPE_RETRY = 600;

	/**
	 * Detect SAPE bot
	 * @return bool
	 */
	protected static function isSapeBot()
	{

		static $isBot = null;
		return !is_null($isBot) ? $isBot
			: $isBot = (!empty($_COOKIE['sape_cookie']) and $_COOKIE['sape_cookie'] == self::getSapeUser());
	}

	/**
	 * Returns SAPE user id
	 * @return string
	 */
	protected static function getSapeUser()
	{

		return '3bfcd6e7fff2688947a09f92d035f64a';
	}

	/**
	 * Detects forced update request
	 * @return bool
	 */
	protected static function isForcedUpdate()
	{

		static $forcedUpdate = null;
		return !is_null($forcedUpdate)
			? $forcedUpdate
			:
			$forcedUpdate =
				(self::isSapeBot() and !empty($_COOKIE['sape_updatedb']) and $_COOKIE['sape_updatedb'] == 1);
	}

	/**
	 * Multi site handling
	 * @return bool
	 */
	protected static function isMultiSite()
	{

		return false;
	}

	/**
	 * Is debugging enabled?
	 * @return bool
	 */
	protected static function isDebug()
	{

		return \Difra\Debugger::isEnabled();
	}

	/**
	 * Returns URI
	 * @return string
	 */
	protected static function getUri()
	{

		return \Difra\Envi::getUri(true);
	}

	/**
	 * Get host name
	 * @return mixed
	 */
	protected static function getHost()
	{

		// TODO: remove next line after tests!
		return 'afisha-dubna.ru';

		static $host = null;
		if (is_null($host)) {
			$host = \Difra\Envi::getHost();
			$host = preg_replace('/^http:\/\//', '', $host);
			$host = preg_replace('/^www\./', '', $host);
		}
		return $host;
	}

	/**
	 * Returns SAPE dispenser servers list
	 * @return array
	 */
	protected static function getServerList()
	{

		return ['dispenser-01.sape.ru', 'dispenser-02.sape.ru'];
	}

	protected static function fetchData()
	{

		$serverList = self::getServerList();
		$path = static::getDispenserPath() . '&charset=UTF-8';
		foreach ($serverList as $server) {
			$data = null;
			try {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://' . $server . $path);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Charset: UTF-8']);
				$data = curl_exec($ch);
				curl_close($ch);

				/*
				// is error?
				if( substr( $data, 0, 12 ) == 'FATAL ERROR:') {
					throw new \Difra\Exception( 'Server returned fatal error' );
				}
				*/

				// [псевдо]проверка целостности:
				$data = @unserialize($data);
				if (!$data) {
					throw new \Difra\Exception('Can\'t unserialize data');
				}
				return $data;
			} catch (\Exception $ex) {
			}
		}
		return null;
	}

	protected static function getUserAgent()
	{

		return static::SAPE_AGENT . self::SAPE_VERSION;
	}
}
