<?php

class IframeController extends Difra\Controller {

	public function indexAction() {

		echo( <<<EOH
<html>
	<head></head>
	<body></body>
</html>
EOH
);
		$this->view->rendered = true;
	}
}