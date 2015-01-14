<?php

///////////////////////////// дневное изменение plays

date_default_timezone_set( 'Europe/Moscow' );

$libPath = dirname( __FILE__ ) . '/../../../fw/lib/';
require($libPath . 'autoloader.php');

$_SERVER['VHOST_NAME'] = 'musiq';

$site = Difra\Site::getInstance();
$db = Difra\MySQL::getInstance();

$db->query( "UPDATE `radio_tracks` SET `plays`=`plays`*0.95, `weight`=`weight`*0.95" );

