<?php

namespace Difra;

class Config {

	/** @var array */
	private $config = null;
	private $modified = false;

	private $defaultConfig = array(
		'instances' => array(
			'main' => array(
				'withAll' => true
			),
			'adm' => array(
				'withAll' => true
			)
		)
	);

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
			$this->config = $this->loadFileConfigs();
			$db           = MySQL::getInstance();
			$conf         = $db->fetchOne( 'SELECT `config` FROM `config` LIMIT 1' );
			$dbconf       = @unserialize( $conf );
			if( is_array( $dbconf ) ) {
				$this->config = $this->merge( $this->config, $dbconf );
			}
			$cache->put( 'config', $this->config );
		} catch( Exception $e ) {
			$this->config = array();
		}
	}

	function merge( $a1, $a2 ) {

		foreach( $a2 as $k => $v ) {
			if( array_key_exists( $k, $a1 ) && is_array( $v ) )
				$a1[$k] = $this->merge( $a1[$k], $a2[$k] );

			else
				$a1[$k] = $v;
		}

		return $a1;
	}
	/**
	 * Получение конфига из config.php
	 *
	 * @return array
	 */
	private function loadFileConfigs() {

		static $newConfig = null;
		if( is_null( $newConfig ) ) {
			$newConfig = $this->defaultConfig;
			if( is_file( DIR_ROOT . '/config.php' ) ) {
				$conf2 = include( DIR_ROOT . '/config.php' );
				$newConfig = $this->merge( $newConfig, $conf2 );
			}
			if( is_file( DIR_SITE . '/config.php' ) ) {
				$conf2         = include( DIR_SITE . '/config.php' );
				$newConfig = $this->merge( $newConfig, $conf2 );
			}
		}
		return $newConfig;
	}

	/**
	 * Возвращает разницу между дефолтным и текущим конфигами
	 * @return array
	 */
	private function diff() {

		$defaultConfig = $this->loadFileConfigs();
		return $this->subDiff( $defaultConfig, $this->config );
	}

	/**
	 * Рекурсивная часть для $this->diff()
	 *
	 * @param array $a1
	 * @param array $a2
	 *
	 * @return array
	 */
	private function subDiff( &$a1, &$a2 ) {

		if( empty( $a2 ) ) {
			return array();
		}
		$diff = array();
		foreach( $a2 as $k => $v ) {
			if( !isset( $a1[$k] ) ) {
				$diff[$k] = $v;
			} elseif( is_array( $v ) ) {
				$d = $this->subDiff( $a1[$k], $a2[$k] );
				if( !empty( $d ) ) {
					$diff[$k] = $d;
				}
			} elseif( $a1[$k] !== $v ) {
				$diff[$k] = $v;
			}
		}
		return $diff;
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
		$diff = $this->diff();
		$db->query( "INSERT INTO `config` SET `config`='" . $db->escape( serialize( $diff ) ) . "'" );
		Cache::getInstance()->remove( 'config' );
		$this->modified = false;
	}

	/**
	 * Получение значение настройки
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get( $key ) {

		$this->load();
		return isset( $this->config[$key] ) ? $this->config[$key] : null;
	}

	/**
	 * Установка значения настройки
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set( $key, $value ) {

		$this->load();
		$this->config[$key] = $value;
		$this->modified     = true;
	}

	/**
	 * Получение значения элемента массива настроек
	 * @param string $key
	 * @param string $arrayKey
	 *
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
	 * @param mixed  $arrayValue
	 */
	public function setValue( $key, $arrayKey, $arrayValue ) {

		$this->load();
		if( !isset( $this->config[$key] ) ) {
			$this->config[$key] = array();
		}
		$this->config[$key][$arrayKey] = $arrayValue;
		$this->modified                = true;
	}

	/**
	 * Получить полный конфиг
	 * @return array
	 */
	public function getConfig() {

		$this->load();
		return $this->config;
	}

	/**
	 * Получить измененные настройки
	 * @return array
	 */
	public function getDiff() {

		$this->load();
		return $this->diff();
	}

	public function getTxtDiff() {

		$diff = $this->getDiff();
		$txtDiff = array();
		$this->txtDiff( $txtDiff, $diff );
		$d2 = array();
		foreach( $txtDiff as $k => $v ) {
			$d2[] = "$k => $v";
		}
		return implode( "\n", $d2 );
	}

	private function txtDiff( &$t, &$d, $pref = '' ) {

		if( !is_array( $d ) ) {
			$t[$pref] = $d;
		} else {
			foreach( $d as $k => $v ) {
				$this->txtDiff( $t, $v, $pref ? "$pref/$k" : $k );
			}
		}
	}

	/**
	 * Сбросить конфиг к дефолтному
	 */
	public function reset() {

		$this->load();
		$this->config = array();
		$this->modified = true;
		$this->save();
	}
}