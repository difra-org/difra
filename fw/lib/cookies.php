<?php

namespace Difra;

/**
 * Cookies
 * @desc Работа с куками
 * @package fw
 * @version 0.1 
 * @access public
 */
class Cookies {
	
	private $expireTime = 0;
	private $domain = null;
	private $path = null;
	
	/**
	 * Cookies::getInstance()
	 * @desc Синглтон
	 * @return Cookies
	 */
	static public function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}
	
	/**
	 * Cookies::__construct()
	 * @return void
	 */
	private function __construct() {
		$this->domain = Site::getInstance()->getMainhost();
		if( strstr( $this->domain, 'www.' ) !== false ) {
			$this->domain = str_replace( 'www.', '.', $this->domain );
		} else {
			$this->domain = '.' . $this->domain; 
		}
		$this->path = '/';
	}
	
	/**
	 * Cookies::setPath()
	 * @desc Устанавливает путь
	 * @param string $path
	 * @return void
	 */
	public function setPath( $path ) {
		$this->path = $path;
	}
	
	/**
	 * Cookies::setDomain()
	 * @desc Устанавливает домен
	 * @param string $domain
	 * @return void
	 */
	public function setDomain( $domain ) {
		$this->domain = $domain;
	}
	
	/**
	 * Cookies::setExpire()
	 * @desc Устанавливает время жизни
	 * @param integer $expireTime
	 * @return void
	 */
	public function setExpire( $expireTime ) {
		$this->expireTime = $expireTime;  
	}
	
	/**
	 * Cookies::set()
	 * @desc Устанавливает куку
	 * @param string $name
	 * @param string || array $value
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
	 * @desc Удаляет куку
	 * @param string $name
	 * @return boolean
	 */
	public function remove( $name ) {
		return setcookie( $name, '', time() - 108000, $this->path );
	}

	public function notify( $message, $error = false ) {

		$this->set( 'notification', array(
						 'type' => $error ? 'error' : 'ok',
						 'message' => (string) $message
					    ) );
	}
}
	
