<?php

namespace Difra;

/**
 * @deprecated
 */
class Capcha {

	static public function getInstance() {

		trigger_error( 'Class \Difra\Capcha is deprecated. Please use \Difra\Libs\Capcha.', E_USER_DEPRECATED );
		return \Difra\Libs\Capcha::getInstance();
	}
}