<?php

namespace Difra\Minify;

class JS extends Common {
	
	public function minify( $data ) {
		
		return JS\JSMin::minify( $data );
	}
}
	
