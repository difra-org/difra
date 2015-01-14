<?php

namespace Difra\Plugins\Widgets;

class Plugin extends \Difra\Plugin {

	protected $require = false;
	protected $version = 5;
	protected $description = 'Web widgets';

	protected $objects = array(
		'Difra\\Plugins\\Widgets\\Objects\\Directory'
	);

	public function init() {
	}

	public function getSitemap() {
	}
}
