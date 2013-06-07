<?php

require_once( __DIR__ . '/autoloader.php' );
require_once( __DIR__ . '/site.php' );
require_once( __DIR__ . '/events.php' );

// Установка констант путей для запуска из PHAR
if( isset( $_ ) ) {
	define( 'DIR_ROOT', dirname( $_ ) . '/' );
	define( 'DIR_PHAR', __DIR__ . '/../../' );
}

if( !empty( $_SERVER['REQUEST_METHOD'] ) ) {
	// web run exec
	\Difra\Site::setMode( 'web' );
	\Difra\Events::getInstance()->run();
} elseif( isset( $_ ) ) {
	// phar cli exec
	\Difra\Site::setMode( 'cli' );
	\Difra\Events::getInstance()->run();
} else {
	// движок подключен из теста или другого скрипта
	\Difra\Site::setMode( 'include' );
	\Difra\Events::getInstance()->run();
}