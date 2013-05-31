<?php

namespace Difra\Param;

class AjaxSafeHTML extends Common {

	const source = 'ajax';
	const type = 'html';
	const named = true;
	const filtered = true;

	use Traits\HTML;

	/**
	 * Конструктор
	 * @param string $value
	 */
	public function __construct( $value = '' ) {

		$this->raw = $value;
		$this->value = \Difra\Param\Filters\HTML::getInstance()->process( $value, self::filtered );
	}
}
