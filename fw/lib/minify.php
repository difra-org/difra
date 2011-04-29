<?php

class Minify {

	static public function getInstance( $type ) {
		
		// no minify for developers' hosts
		if( Site::getInstance()->devMode ) {
			return Minify_None::getInstance();
		}
		switch( $type ) {
			case 'css':
				return Minify_CSS::getInstance();
			default:
				return Minify_None::getInstance();
		}
	}
	
}