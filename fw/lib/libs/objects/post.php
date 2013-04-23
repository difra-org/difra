<?php

namespace Difra\Libs\Objects;

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