<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

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
	public $oldLink = null;
}