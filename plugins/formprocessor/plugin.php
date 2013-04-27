<?php

namespace Difra\Plugins\FormProcessor;

class Plugin extends \Difra\Plugin {

	protected $version = 3.1;
	protected $description = 'Custom feedback forms';

	public function init() {

        \Difra\Events::register( 'pre-action', '\Difra\Plugins\FormProcessor', 'run' );
    }
}
