<?php

namespace Difra\Plugins\CMS;

/**
 * Class Plugin
 *
 * @package Difra\Plugins\CMS
 */
class Plugin extends \Difra\Plugin {

	/** @var int */
	protected $version = 5;
	/** @var string */
	protected $description = 'Content management system';
	/** @var array */
	protected $require = array( 'mysql', 'editor' );

	public function init() {

		\Difra\Events::register( 'pre-action', '\Difra\Plugins\CMS', 'run' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'addMenuXML' );
		\Difra\Events::register( 'dispatch', '\Difra\Plugins\CMS', 'getSnippets' );
	}

	/**
	 * @return array|bool
	 */
	public function getSitemap() {

		return \Difra\Plugins\CMS::getSitemap();
	}
}
