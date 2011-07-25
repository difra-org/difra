<?php

namespace Difra;

class Minify {

	static public function getInstance( $type ) {
		
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
