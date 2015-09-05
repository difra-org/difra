<?php

namespace Difra\Plugins\Tasker;

/**
 * Class Plugin
 * @package Difra\Plugins\Tasker
 */
class Plugin extends \Difra\Plugin
{
	protected $require = ['users'];
	protected $version = 4;
	protected $description = 'Task tracker';
	protected $objects = [
		'company' => 'Difra\\Plugins\\Tasker\\Objects\\Company',
		'department' => 'Difra\\Plugins\\Tasker\\Objects\\Department',
		'department2employee' => 'Difra\\Plugins\\Tasker\\Objects\\Department2Employee',
		'employee' => 'Difra\\Plugins\\Tasker\\Objects\\Employee',
		'priority' => 'Difra\\Plugins\\Tasker\\Objects\\Priority',
		'project' => 'Difra\\Plugins\\Tasker\\Objects\\Project',
		'task' => 'Difra\\Plugins\\Tasker\\Objects\\Task',
	];

	public function init()
	{
	}

	public function getSitemap()
	{
	}
}
