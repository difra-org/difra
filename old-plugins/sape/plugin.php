<?php

namespace Difra\Plugins\SAPE;

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
	protected $require = ['mysql'];

	public function init()
	{

		\Difra\Events::register('dispatch', '\Difra\Plugins\SAPE', 'addXML');
		\Difra\Events::register('dispatch', '\Difra\Plugins\SAPE', 'addSitemapHTML');
	}
}
