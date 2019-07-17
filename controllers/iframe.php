<?php

namespace Controller;

class Iframe extends \Difra\Controller
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
