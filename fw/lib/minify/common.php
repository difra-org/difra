<?php

	abstract class Minify_Common {
		
		abstract function minify( $data );
		
		static public function getInstance() {
			
			$_instances = array();
			$name = get_called_class();
			return isset( $_instances[$name] ) ? $_instances[$name]  : $_instances[$name] = new $name();
		}
	}