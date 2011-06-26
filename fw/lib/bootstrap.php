<?php

require_once( dirname( __FILE__ ) . '/autoloader.php' );

// load site
Difra\Site::getInstance();
// load plugins
Difra\Plugger::getInstance();
// call controller
Difra\Action::getInstance()->run();

