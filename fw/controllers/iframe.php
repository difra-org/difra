<?php

/**
 * Class IframeController
 * Displays empty page for iframes' src attribute.
 */
class IframeController extends Difra\Controller
{
    public function indexAction()
    {
        echo(<<<EOH
<html>
	<head></head>
	<body></body>
</html>
EOH
        );
        \Difra\View::$rendered = true;
    }
}