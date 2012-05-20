<?php

namespace Difra;

class Config {

	private $config = null;
	private $dbconf = array();
	private $modified = false;

	/**
	 * Синглтон
	 * @static
	 * @return Config
	 */
	static public function getInstance() {
		static $instance;
		return $instance ? $instance : $instance = new self;
	}

	/**
	 * Деструктор
	 */
	public function __destruct() {

		$this->save();
	}

	/**
	 * Загрузка настроек
	 */
	private function load() {

		if( !is_null( $this->config ) ) {
			return;
		}
		try {
			$cache = Cache::getInstance();
			if( $c = $cache->get( 'config' ) and !Debugger::getInstance()->isEnabled() ) {
				$this->config = $c;
				return;
			}
			$this->config = array();
			if( is_file( DIR_ROOT . '/config.php' ) ) {
				$this->config = include( DIR_ROOT . '/config.php' );
			}
			if( is_file( DIR_SITE . '/config.php' ) ) {
				$conf         = include( DIR_SITE . '/config.php' );
				$this->config = array_merge( $this->config, $conf );
			}
			$db = MySQL::getInstance();
			$conf = $db->fetchOne( 'SELECT `config` FROM `config`' );
			$this->dbconf = @unserialize( $conf );
			if( is_array( $this->dbconf ) ) {
				$this->config = array_merge( $this->config, $this->dbconf );
			} else {
				$this->dbconf = array();
			}
			$cache->put( 'config', $this->config );
		} catch( Exception $e ) {
			$this->config = array();
		}
	}

	/**
	 * Сохранение настроек
	 */
	private function save() {

		if( !$this->modified ) {
			return;
		}
		$db = MySQL::getInstance();
		$db->query( 'DELETE FROM `config`' );
		$db->query( "INSERT INTO `config` SET `config`='" . $db->escape( serialize( $this->dbconf ) ) . "'" );
		Cache::getInstance()->remove( 'config' );
		$this->modified = false;
	}

	/**
	 * Получение значение настройки
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key ) {

		$this->load();
		return isset( $this->config[$key] ) ? $this->config[$key] : null;
	}

	/**
	 * Установка значения настройки
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {

		$this->load();
		$this->config[$key] = $this->dbconf[$key] = $value;
		$this->modified     = true;
	}

	/**
	 * Получение значения элемента массива настроек
	 * @param string $key
	 * @param string $arrayKey
	 * @return mixed
	 */
	public function getValue( $key, $arrayKey ) {

		$this->load();
		return isset( $this->config[$key][$arrayKey] ) ? $this->config[$key][$arrayKey] : null;
	}

	/**
	 * Установка значения элемента массива настроек
	 * @param string $key
	 * @param string $arrayKey
	 * @param mixed $arrayValue
	 */
	public function setValue( $key, $arrayKey, $arrayValue ) {

		$this->load();
		if( !isset( $this->config[$key] ) ) {
			$this->config[$key] = array();
		}
		if( !isset( $this->dbconf[$key] ) ) {
			$this->dbconf[$key] = array();
		}
		$this->config[$key][$arrayKey] = $this->dbconf[$key][$arrayKey] = $arrayValue;
		$this->modified = true;
	}
}