<?php

namespace Difra\Plugins\CDN;

class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CDN', 'selectHost' );
	}
}
