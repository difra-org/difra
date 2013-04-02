<?php

require_once( __DIR__ . '/autoloader.php' );

// Установка констант путей для запуска из PHAR
if( isset( $_ ) ) {
	define( 'DIR_ROOT', dirname( $_ ) . '/' );
	define( 'DIR_PHAR', __DIR__ . '/../../' );
}

if( !empty( $_SERVER['REQUEST_METHOD'] ) ) {
	// web run exec
	\Difra\Events::getInstance()->run();
} elseif( isset( $_ ) ) {
	// phar cli exec
}