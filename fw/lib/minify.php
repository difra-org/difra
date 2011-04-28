<?php

class Minify {

	static public function getInstance( $type ) {
		
		switch( $type ) {
			case 'css':
				return Minify_CSS::getInstance();
			default:
				return Minify_None::getInstance();
		}
	}
	
}