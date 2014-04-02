<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Param;

/**
 * Class AjaxCheckbox
 *
 * @package Difra\Param
 */
class AjaxCheckbox extends Common {

	const source = 'ajax';
	const type = 'string';
	const named = true;
	const auto = true;

	public function __construct( $value = '' ) {

		$this->value = $value ? true : false;
	}
}
