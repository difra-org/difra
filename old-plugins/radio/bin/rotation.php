<?php

///////////////////////////// ротация канала

$libPath = dirname( __FILE__ ) . '/../../../fw/lib/';
require($libPath . 'autoloader.php');

$_SERVER['VHOST_NAME'] = 'musiq';

if( !isset( $_REQUEST['name'] ) || $_REQUEST['name']=='' ) {
	die( 'Нет параметра с именем канала!' . "\n\n" );
}

$site = Difra\Site::getInstance();
$site->init();
$db = Difra\MySQL::getInstance();

$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $_REQUEST['name'] );

$track = $Channel->getTrackToPlay();

echo $track;

echo "\n";


