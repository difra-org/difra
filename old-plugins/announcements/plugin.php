<?php

namespace Difra\Plugins\Announcements;

class Plugin extends \Difra\Plugin
{
	protected $require = ['users', 'editor', 'jqueryui', 'mysql'];
	protected $version = 5;
	protected $description = 'Announcements';

	public function init()
	{
	}

	public function getSitemap()
	{
		return \Difra\Plugins\Announcements::getMap();
	}
}
