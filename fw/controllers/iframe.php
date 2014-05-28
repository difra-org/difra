<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Class IframeController
 */
class IframeController extends Difra\Controller {

	public function indexAction() {

		echo( <<<EOH
<html>
	<head></head>
	<body></body>
</html>
EOH
		);
		\Difra\View::$rendered = true;
	}
}