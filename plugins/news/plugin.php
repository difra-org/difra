<?php

namespace Difra\Plugins\News;

class Plugin extends \Difra\Plugin {

    protected $require = array( 'jqueryui', 'editor' );

    public function init() {
    }

    public function getSitemap() {

        return \Difra\Plugins\News::getInstance()->getSiteMapArray();

    }
}
