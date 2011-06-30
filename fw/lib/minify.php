<?php

namespace Difra;

class Minify {

	static public function getInstance( $type ) {
		
		// no minification in debugging mode
		if( Debugger::getInstance()->isEnabled() ) {
			return Minify\None::getInstance();
		}
		switch( $type ) {
			case 'css':
				return Minify\CSS::getInstance();
			case 'js':
				return Minify\JS::getInstance();
			default:
				return Minify\None::getInstance();
		}
	}
	
}
