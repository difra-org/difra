<?php

// Установка констант путей для запуска из PHAR
if( isset( $_ ) ) {
	define( 'DIR_ROOT', dirname( $_ ) . '/' );
	define( 'DIR_PHAR', dirname( dirname( __DIR__ ) ) . '/' );
	define( 'DIR_FW', DIR_PHAR . 'fw/' );
	define( 'DIR_PLUGINS', DIR_PHAR . 'plugins/' );
} else {
	define( 'DIR_ROOT', dirname( dirname( __DIR__ ) ) . '/' );
	define( 'DIR_FW', DIR_ROOT . 'fw/' );
	define( 'DIR_PLUGINS', DIR_ROOT . 'plugins/' );
}
require_once( DIR_FW . 'lib/envi.php' );
define( 'DIR_SITE', DIR_ROOT . 'sites/' . \Difra\Envi::getSite() . '/' );
define( 'DIR_DATA', !empty( $_SERVER['VHOST_DATA'] ) ? $_SERVER['VHOST_DATA'] . '/' : DIR_ROOT . 'data/' );
require_once( DIR_FW . 'lib/autoloader.php' );

\Difra\Envi::setMode( !empty( $_SERVER['REQUEST_METHOD'] ) ? 'web' : 'cli' );
\Difra\Events::run();
