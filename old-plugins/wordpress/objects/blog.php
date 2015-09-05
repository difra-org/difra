<?php

namespace Difra\Libs\Objects;

/**
 * Class Blog
 * @package Difra\Libs\Objects
 * @deprecated
 */
class Blog
{
	public $posts = [];
	public $pages = [];

	public function addPost($post)
	{

		$this->posts[] = $post;
	}
}
