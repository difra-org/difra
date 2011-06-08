<?php

include_once( 'js/jsmin.php' );
	
class Minify_JS extends Minify_Common {
	
	public function minify( $data ) {
		
		return JSMin::minify( $data );
	}
}
	
