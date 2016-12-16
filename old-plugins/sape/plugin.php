<?php

namespace Difra\Plugins\SAPE;

use Difra\Events;

/**
 * Class Plugin
 * @package Difra\Plugins\SAPE
 */
class Plugin extends \Difra\Plugin
{
	/** @var int */
	protected $version = 5;
	/** @var string */
	protected $description = 'SAPE webmaster support';
	/** @var array */
	protected $require = ['database'];

	public function init()
	{

		Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\SAPE', 'addXML');
		Events::register(Events::EVENT_ACTION_DONE, '\Difra\Plugins\SAPE', 'addSitemapHTML');
	}
}
