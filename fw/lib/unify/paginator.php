<?php

namespace Difra\Unify;

/**
 * Пагинатор
 * Class Paginator
 *
 * @package Difra\Unify
 */
class Paginator {

	public $perpage = 20;
	public $page = 1;

	public $total = null;
	public $pages = null;

	public $linkPrefix = '';

	public $get = false;
}