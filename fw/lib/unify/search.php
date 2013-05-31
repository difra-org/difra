<?php

namespace Difra\Unify;

/**
 * Поиск
 * Class Search
 *
 * @package Difra\Unify
 */
class Search {

	/** @var string[]|null По каким классам искать. Если null, то по всем. */
	public $classes = null;
	/** @var string[string] */
	public $filters = array();
	/** @var string */
	public $text = null;

	public $paginator = null;
	public $sort = array();

}