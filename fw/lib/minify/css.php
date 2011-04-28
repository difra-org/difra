<?php
	
	class Minify_CSS extends Minify_Common {
		
		public function minify( $data ) {
			
			$data = preg_replace( '/\/\*.*?\*\//s', '', $data );	// remove comments
			$data = preg_replace( '/\s+/', ' ', $data );		// remove replace multiple whitespaces with space
			$data = preg_replace( '/\s?{\s/', '{', $data );		// remove spaces near {
			$data = preg_replace( '/\s?}\s/', '}', $data );		// remove spaces near }
			$data = preg_replace( '/\s?;\s/', ';', $data );		// remove spaces near ;
			$data = preg_replace( '/\s?:\s/', ':', $data );		// remove spaces near :
			$data = preg_replace( '/\s?,\s/', ',', $data );		// remove spaces near ,
			return $data;
		}
	}
	
