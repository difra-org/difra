<?php

namespace Difra\Param;

class AjaxSafeHTML extends Common {

	const source = 'ajax';
	const type = 'string';
	const named = true;

	public function __construct( $value = '' ) {

		$this->value = Filters\HTML::getInstance()->process( $value );
	}
}
