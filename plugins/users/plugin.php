<?php

namespace Difra\Plugins\Users;
class Plugin extends \Difra\Plugin {

	protected $provides = 'auth';
	protected $require = 'mysql';
	protected $version = '3.1';
	protected $description = 'User accounts';

	public function init() {

		\Difra\Events::register( 'config', '\Difra\Plugins\Users', 'checkLongSession' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\Users', 'dispatch' );
	}
}
