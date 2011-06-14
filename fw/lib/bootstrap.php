<?php

require_once( dirname( __FILE__ ) . '/autoloader.php' );

// TODO: избавиться от этого файла ;)
require_once( dirname( __FILE__ ) . '/compat.php' );

// load site
Difra\Site::getInstance();
// load plugins
Difra\Plugger::getInstance();
// call controller
Difra\Action::getInstance()->run();

