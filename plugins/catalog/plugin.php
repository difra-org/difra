<?php

namespace Difra\Plugins\Catalog;
class Plugin extends \Difra\Plugin {

	public function init() {

		\Difra\Events::register( 'dispatch', '\Difra\Plugins\Catalog', 'addCategoryXML' );
	}
}
