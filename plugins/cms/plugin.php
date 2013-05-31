<?php

namespace Difra\Plugins\CMS;

class Plugin extends \Difra\Plugin {

	protected $version = 4;
	protected $description = 'Content management system';
	protected $require = array( 'mysql', 'editor' );

	public function init() {

		\Difra\Events::register( 'pre-action', '\Difra\Plugins\CMS', 'run' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'addMenuXML' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'getSnippets' );
	}

	public function getSitemap() {

		return \Difra\Plugins\CMS::getSitemap();
	}
}
