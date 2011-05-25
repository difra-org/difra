<?php

class Minify {

	static public function getInstance( $type ) {
		
		// no minification in debugging mode
		if( Debugger::getInstance()->isEnabled() ) {
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