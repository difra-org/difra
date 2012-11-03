<?php

namespace Difra\Plugins\VideoManager;

class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'dispatch', '\Difra\Plugins\videoManager', 'getHttpPath' );
	}
}
