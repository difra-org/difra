<?php

namespace Difra\Plugins\Portfolio;

class Plugin extends \Difra\Plugin {

	protected $require = array( 'editor' );
	protected $version = 5;
	protected $description = 'Portfolio';

	protected $objects = array(
		'Difra\\Plugins\\Portfolio\\Objects\\Entry'
	);

	public function init() {
	}

	public function getSitemap() {
	}
}
