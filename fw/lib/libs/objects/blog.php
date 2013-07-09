<?php

namespace Difra\Libs\Objects;

/**
 * Class Blog
 *
 * @package Difra\Libs\Objects
 * @deprecated
 */
class Blog {
	public $posts = array();
	public $pages = array();

	public function addPost( $post ) {

		$this->posts[] = $post;
	}
}