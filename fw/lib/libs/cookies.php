<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Libs;

use Difra\Envi;

/**
 * Cookies
 *
 * @desc    Работа с куками
 * @package fw
 * @version 0.1
 * @access  public
 */
class Cookies {

	private $expireTime = 0;
	private $domain = null;
	private $path = null;

	/**
	 * Cookies::getInstance()
	 *
	 * @desc Синглтон
	 * @return Cookies
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Cookies::__construct()
	 */
	private function __construct() {

		$this->domain = Envi::getHost( true );
		$this->domain = ( substr( $this->domain, 0, 4 ) == 'www.' ) ? substr( $this->domain, 3 ) : ( '.' . $this->domain );
		$this->path = '/';
	}

	/**
	 * Cookies::setPath()
	 *
	 * @desc Устанавливает путь
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function setPath( $path ) {

		$this->path = $path;
	}

	/**
	 * Cookies::setDomain()
	 *
	 * @desc Устанавливает домен
	 *
	 * @param string $domain
	 *
	 * @return void
	 */
	public function setDomain( $domain ) {

		$this->domain = $domain;
	}

	/**
	 * Cookies::setExpire()
	 *
	 * @desc Устанавливает время жизни
	 *
	 * @param integer $expireTime
	 *
	 * @return void
	 */
	public function setExpire( $expireTime ) {

		$this->expireTime = $expireTime;
	}

	/**
	 * Cookies::set()
	 *
	 * @desc Устанавливает куку
	 *
	 * @param string       $name
	 * @param string|array $value
	 *
	 * @return boolean
	 */
	public function set( $name, $value ) {

		if( is_array( $value ) ) {
			$value = json_encode( $value );
		}
		return setrawcookie( $name, rawurlencode( $value ), $this->expireTime, $this->path, $this->domain );
	}

	/**
	 * Cookies::remove()
	 *
	 * @desc Удаляет куку
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function remove( $name ) {

		return setrawcookie( $name, '', time() - 108000, $this->path, $this->domain );
	}

	/**
	 * Sets cookie that makes Ajaxer show notification popup.
	 *
	 * @param      $message
	 * @param bool $error
	 */
	public function notify( $message, $error = false ) {

		$this->set( 'notify',
			    array(
				    'type' => $error ? 'error' : 'ok',
				    'message' => (string)$message,
				    'lang' => array(
					    'close' => \Difra\Locales::getInstance()->getXPath( 'notifications/close' )
				    )
			    ) );
	}

	/**
	 * Sets cookie that makes Ajaxer request some URL.
	 *
	 * @param $url
	 *
	 * @return void
	 */
	public function query( $url ) {

		$this->set( 'query', array( 'url' => $url ) );
	}
}
	
