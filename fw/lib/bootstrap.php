<?php

require_once( dirname( __FILE__ ) . '/autoloader.php' );

// load site
Difra\Site::getInstance();
Difra\Events::getInstance()->run();

