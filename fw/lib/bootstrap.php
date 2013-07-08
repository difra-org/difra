<?php

require_once( __DIR__ . '/autoloader.php' );
require_once( __DIR__ . '/envi.php' );
require_once( __DIR__ . '/events.php' );

// Установка констант путей для запуска из PHAR
if( isset( $_ ) ) {
	define( 'DIR_ROOT', dirname( $_ ) . '/' );
	define( 'DIR_PHAR', __DIR__ . '/../../' );
}

if( !empty( $_SERVER['REQUEST_METHOD'] ) ) {
	\Difra\Envi::setMode( 'web' );
	\Difra\Events::run();
} elseif( isset( $_ ) ) {
	\Difra\Envi::setMode( 'cli' );
	\Difra\Events::run();
} else {
	\Difra\Envi::setMode( 'cli' );
	\Difra\Events::run();
}