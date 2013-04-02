<?php

namespace Difra\Plugins\FormProcessor;

class Plugin extends \Difra\Plugin {

    public function init() {

        \Difra\Events::register( 'pre-action', '\Difra\Plugins\FormProcessor', 'run' );
    }
}
