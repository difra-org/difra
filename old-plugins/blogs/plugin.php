<?php

namespace Difra\Plugins\Blogs;

class Plugin extends \Difra\Plugin
{
	protected $require = ['mysql', 'users'];
	protected $version = 3.1;
	protected $description = 'Blogs';

	public function init()
	{
	}
}

