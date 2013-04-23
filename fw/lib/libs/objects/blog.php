<?php

namespace Difra\Libs\Objects;

class Blog {
	public $posts = array();
	public $pages = array();

	public function addPost( $post ) {

		$this->posts[] = $post;
	}
}