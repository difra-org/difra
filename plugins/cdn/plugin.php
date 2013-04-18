<?php

namespace Difra\Plugins\CDN;

class Plugin extends \Difra\Plugin {

	protected $version = 3.1;
	protected $description = 'Content delivery network';
	protected $require = 'mysql';

	public function init() {

		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CDN', 'selectHost' );
	}
}
