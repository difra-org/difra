<?php

namespace Difra;

class Images {

	static public function getInstance() {

		trigger_error( 'Class \Difra\Images is deprecated. Please use \Difra\Libs\Images.', E_USER_DEPRECATED );
		return \Difra\Libs\Images::getInstance();
	}
}