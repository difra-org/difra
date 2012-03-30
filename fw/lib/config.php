<?php

namespace Difra;

class Config {

	private $config = null;
	private $modified = false;

	static public function getInstance() {
		static $instance;
		return $instance ? $instance : $instance = new self;
	}

	public function __destruct() {

		$this->save();
	}

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

	public function get( $key ) {

		$this->load();
		return isset( $this->config[$key] ) ? $this->config[$key] : null;
	}

	public function set( $key, $value ) {

		$this->load();
		$this->config[$key] = $value;
		$this->modified = true;
	}
}