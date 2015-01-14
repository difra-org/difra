<?php

//date_default_timezone_set( 'Europe/Moscow' );

// проверяем параметры

if( !isset( $_REQUEST['vhost'] ) || $_REQUEST['vhost']=='' ) {

	echo "\n";
	echo "Для работы проверялки CDN хостов нужно делать так: \n";
	echo "checkHosts.php vhost=ИМЯ ВИРТУАЛЬНОГО ХОСТА \n\n";
	die();
}

$_SERVER['VHOST_NAME'] = trim( strtolower( $_REQUEST['vhost'] ) );
$_SERVER['VHOST_DATA'] = null;

$libPath = dirname( __FILE__ ) . '/../../../fw/lib/';
require($libPath . 'autoloader.php');

$site = Difra\Site::getInstance();
$site->init();
$db = Difra\MySQL::getInstance();

$CDN = \Difra\Plugins\CDN::getInstance();

// массовая проверка хостов

$CDN->checkHosts();
