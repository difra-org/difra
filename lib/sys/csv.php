<?php

// unfinished class for csv file type
class CSV {
	public function import( $text ) {

		// get lines
		$text = str_replace( "\r", '', $text );
		$lines = explode( "\n", $text );

		// clear empty lines at file end
		while( !$lines[sizeof( $lines ) - 1] ) {
			unset( $lines[sizeof( $lines ) - 1 ] );
		}

		foreach( $lines as $line ) {
			var_dump( str_getcsv( $line ) );
		}
		return array();
	}
}
