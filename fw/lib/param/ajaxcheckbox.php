<?php

namespace Difra\Param;

class AjaxCheckbox extends Common {

	const source = 'ajax';
	const type = 'string';
	const named = true;
	const auto = true;

	public function __construct( $value = '' ) {

		$this->value = $value ? 1 : 0;
	}
}
