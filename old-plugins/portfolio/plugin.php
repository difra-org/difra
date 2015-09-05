<?php

namespace Difra\Plugins\Portfolio;

class Plugin extends \Difra\Plugin
{
	protected $require = ['editor'];
	protected $version = 5.1;
	protected $description = 'Portfolio';
	protected $objects = [
		'Difra\\Plugins\\Portfolio\\Objects\\Entry',
		'Difra\\Plugins\\Portfolio\\Objects\\Portfolio',
		'Difra\\Plugins\\Portfolio\\Objects\\Images'
	];

	public function init()
	{
	}

	public function getSitemap()
	{

		return \Difra\Plugins\Portfolio::getSiteMap();
	}
}
