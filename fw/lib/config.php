<?php

namespace Difra;

class Config {

	private $config = null;
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
			}
			$db = MySQL::getInstance();
			$conf = $db->fetchOne( 'SELECT `config` FROM `config`' );
			$this->config = @unserialize( $conf );
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
		$db->query( "INSERT INTO `config` SET `config`='" . $db->escape( serialize( $this->config ) ) . "'" );
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
		$this->config[$key] = $value;
		$this->modified     = true;
	}

	/**
	 * Получение значения настройки (для массивов)
	 * @param string $key
	 * @param string $arrayKey
	 * @return mixed
	 */
	public function getValue( $key, $arrayKey ) {

		$this->load();
		return isset( $this->config[$key][$arrayKey] ) ? $this->config[$key][$arrayKey] : null;
	}

	/**
	 * Установка значения настройки (для массивов)
	 * @param string $key
	 * @param string $arrayKey
	 * @param mixed $arrayValue
	 */
	public function setValue( $key, $arrayKey, $arrayValue ) {

		$this->load();
		if( !isset( $this->config[$key] ) ) {
			$this->config[$key] = array();
		}
		$this->config[$key][$arrayKey] = $arrayValue;
	}
}