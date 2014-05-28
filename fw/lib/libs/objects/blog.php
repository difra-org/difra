<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

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