<?php

namespace Difra;

/**
 * @deprecated
 */
class XML {

	static public function getInstance() {

		trigger_error( 'Class \Difra\XML is deprecated. Please use \Difra\Libs\XML\DOM.', E_USER_DEPRECATED );
		return \Difra\Libs\XML\DOM::getInstance();
	}
}