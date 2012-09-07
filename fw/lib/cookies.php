<?php

namespace Difra;

/**
 * @deprecated
 */
class Cookies {

	static public function getInstance() {

		trigger_error( 'Class \Difra\Cookies is deprecated. Please user \Difra\Libs\Cookies.', E_USER_DEPRECATED );
		return \Difra\Libs\Cookies::getInstance();
	}
}