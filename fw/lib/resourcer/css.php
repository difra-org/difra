<?php

namespace Difra\Resourcer;

use Difra\Libs\Less;

class CSS extends Abstracts\Plain {

	protected $type = 'css';
	protected $printable = true;
	protected $contentType = 'text/css';

	public function processText( $text ) {

		return Less::compile( $text );
	}
}