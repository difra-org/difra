<?php

namespace Difra\Plugins\Widgets\Objects;

class Directory extends \Difra\Unify
{
	const DIRECTORY_LENGTH = 64;
	const NAME_LENGTH = 512;
	static protected $propertiesList = [

		'id' => [
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		],
		'directory' => [
			'type' => 'char',
			'length' => self::DIRECTORY_LENGTH,
			'required' => true,
			'index' => true
		],
		'name' => [
			'type' => 'varchar',
			'length' => self::NAME_LENGTH,
			'required' => true
		],
		'directory_name' => [
			'type' => 'index',
			'columns' => ['directory', 'name']
		]
	];
	static protected $defaultOrder = 'name';
}
