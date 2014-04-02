<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Param;

/**
 * Class AjaxSafeHTML
 *
 * @package Difra\Param
 */
class AjaxSafeHTML extends Common {

	const source = 'ajax';
	const type = 'html';
	const named = true;
	const filtered = true;

	use Traits\HTML;

	/**
	 * Конструктор
	 *
	 * @param string $value
	 */
	public function __construct( $value = '' ) {

		$this->raw = $value;
		$this->value = Filters\HTML::getInstance()->process( $value, self::filtered );
	}
}
