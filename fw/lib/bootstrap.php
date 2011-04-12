<?php

require_once( dirname( __FILE__ ) . '/plugger.php' );
require_once( dirname( __FILE__ ) . '/site.php' );
require_once( dirname( __FILE__ ) . '/autoload.php' );

setlocale( LC_ALL, 'UTF8' ); 

// load site
Site::getInstance();
// load plugins
Plugger::getInstance();
// call controller
Action::getInstance()->run();

