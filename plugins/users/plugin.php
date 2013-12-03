<?php

namespace Difra\Plugins\Users;

class Plugin extends \Difra\Plugin {

	protected $provides = 'auth';
	protected $require = 'mysql';
	protected $version = '5.1';
	protected $description = 'User accounts';

	protected $objects = array(
		'Difra\\Plugins\\Users\\Objects\\User',
		'Difra\\Plugins\\Users\\Objects\\Fields',
		'Difra\\Plugins\\Users\\Objects\\Sessions',
		'Difra\\Plugins\\Users\\Objects\\Recovers'
	);

	public function init() {

		\Difra\Events::register( 'config', '\Difra\Plugins\Users\User', 'checkLongSession' );
	}
}
