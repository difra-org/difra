<?php

function __autoload( $class ) {

	$file = str_replace( '_', '/', strtolower( $class ) ) . '.php';
	$dir = DIR_ROOT . 'lib/';
	if( !file_exists( $dir . $file ) ) {
		$dir .= 'sys/';
	}
	if( file_exists( $dir . $file ) ) {
		include_once( $dir . $file );
	}
}
