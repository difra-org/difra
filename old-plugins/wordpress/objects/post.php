<?php

namespace Difra\Libs\Objects;

/**
 * Class Post
 * @package Difra\Libs\Objects
 * @deprecated
 */
class Post
{
	public $title = '';
	public $pubDate = null;
	public $author = null;
	public $body = '';
	public $categories = [];
	public $additionals = [];
	/** @var Comment[] */
	public $comments = [];
	public $oldLink = null;
}
