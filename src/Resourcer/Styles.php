<?php

namespace Difra\Resourcer;

use Difra\Cache;
use Difra\Controller;
use Difra\Resourcer;
use Difra\View;

class Styles
{
    public static function getInstance()
    {
        static $instance = null;
        return $instance ? $instance : $instance = new self;
    }

    public function view($instance)
    {
        $parts = explode('.', $instance);
        if (sizeof($parts) === 2) {
            if ($parts[1] === 'css') {
                $instance = $parts[0];
            }
        }
        if (!$instance) {
            return false;
        }
        header('Content-Type: text/css');
//        if (!$modified = Cache::getInstance()->get("{$instance}_styles_modified")) {
//            $modified = gmdate('D, d M Y H:i:s') . ' GMT';
//        }
//        View::addExpires(Controller::DEFAULT_CACHE);
//        header('Last-Modified: ' . $modified);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + Resourcer\Abstracts\Common::CACHE_TTL) . ' GMT');

        echo SCSS::getInstance()->compile($instance);
        echo CSS::getInstance()->compile($instance);

        /*
        if ($data = $this->compileGZ($instance)) {
            // header( 'Vary: Accept-Encoding' );
            header('Content-Encoding: gzip');
        } else {
            $data = $this->compile($instance);
        }
        */

        return true;
    }

    public function isPrintable()
    {
        return true;
    }
}