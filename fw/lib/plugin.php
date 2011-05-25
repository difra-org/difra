<?php

abstract class Plugin {

	public function getInstance() {
		
		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

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
}

