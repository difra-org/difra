<?php

namespace Difra\Libs\Objects;

/**
 * Class Post
 *
 * @package Difra\Libs\Objects
 * @deprecated
 */
class Post {

	public $title = '';
	public $pubDate = null;
	public $author = null;
	public $body = '';
	public $categories = array();
	public $additionals = array();
	/** @var Comment[] */
	public $comments = array();
}