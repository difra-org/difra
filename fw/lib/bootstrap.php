<?php

require_once( dirname( __FILE__ ) . '/autoloader.php' );

// load site
Site::getInstance();
// load plugins
Plugger::getInstance();
// call controller
Action::getInstance()->run();

