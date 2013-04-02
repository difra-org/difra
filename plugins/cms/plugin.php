<?php

namespace Difra\Plugins\CMS;

class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'pre-action', '\Difra\Plugins\CMS', 'run' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'addMenuXML' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'getSnippets' );
	}

	public function getSitemap() {

		return \Difra\Plugins\CMS::getSitemap();
	}
}
