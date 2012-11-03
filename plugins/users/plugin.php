<?php

namespace Difra\Plugins\Users;
class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'config', '\Difra\Plugins\Users', 'checkLongSession' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\Users', 'dispatch' );
	}
}
