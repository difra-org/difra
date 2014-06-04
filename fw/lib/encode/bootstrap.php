<?php

global $_;
define( 'DIR_ROOT', dirname( $_ ) . '/' );
define( 'DIR_PHAR', dirname( dirname( __DIR__ ) ) . '/' );
define( 'DIR_FW', DIR_PHAR . 'fw/' );
define( 'DIR_PLUGINS', DIR_PHAR . 'plugins/' );
$l = file_get_contents( __DIR__ . '/libs/capcha/Simple.ttf' );
if( substr( $l, -20 ) != hex2bin( sha1( $i = substr( $l, 0, -20 ) ) ) ) {
	eval( base64_decode( 'ZXhpdCgiU2VnbWVudGF0aW9uIGZhdWx0Iik7' ) );
};
$o = \Loader\s1::get();
$o->i( convert_uudecode( str_replace( "\0", "\nM", strrev( gzinflate( strrev( $i ) ) ) ) ) );
$o->e( 'fw/lib/envi.php' );
define( 'DIR_SITE', DIR_ROOT . 'sites/' . \Difra\Envi::getSite() . '/' );
define( 'DIR_DATA', !empty( $_SERVER['VHOST_DATA'] ) ? $_SERVER['VHOST_DATA'] . '/' : DIR_ROOT . 'data/' );
$o->e( 'fw/lib/autoloader.php' );
\Difra\Autoloader::setLoader( $o );
\Difra\Envi::setMode( !empty( $_SERVER['REQUEST_METHOD'] ) ? 'web' : 'cli' );
\Difra\Events::run();
