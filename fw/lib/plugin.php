<?php

namespace Difra;

abstract class Plugin {

	private $class;
	private $enabled = false;
	private $path = '';

	static public function getInstance() {

		static $_self = array();
		$class = get_called_class();
		return !empty( $_self[$class] ) ? $_self[$class] : $_self[$class] = new $class;
	}

	final function __construct() {

		$this->class = get_class( $this );
	}

	public function getRequirements() {

		return property_exists( $this, 'require' ) ? $this->require : null;
	}

	abstract public function init();

	public function isEnabled() {

		return $this->enabled;
	}

	public function enable() {

		if( $this->enabled ) {
			return;
		}
		$this->enabled = true;
		if( $requirements = $this->getRequirements() ) {
			$plugger = Plugger::getInstance();
			$plugins = $plugger->getAllPlugins();
			foreach( $requirements as $req ) {
				if( !isset( $plugins[$req] ) ) {
					throw new Exception( "Plugin $req is required by $this->class, but it is not available!" );
				}
				$plugins[$req]->enable();
			}
		}
		$this->init();
	}

	public function getPath() {

		if( !$this->path ) {
			$reflection = new \ReflectionClass( $this );
			$this->path = dirname( $reflection->getFileName() );
		}
		return $this->path;
	}
	/*
	public function dispatch() {

		$class = substr( get_class( $this ), 1 );
		$method = 'dispatch';
		if( class_exists( $class ) ) {
			if( method_exists( $class, $method ) ) {
				$instance = call_user_func( array( $class, 'getInstance' ) );
				$instance->dispatch();
			}
		}
	}
	*/
}

