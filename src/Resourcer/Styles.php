<?php

declare(strict_types=1);

namespace Difra\Resourcer;

use Difra\Resourcer;

/**
 * Styles resourcer
 */
class Styles
{
    /**
     * @return \Difra\Resourcer\Styles
     */
    public static function getInstance(): Styles
    {
        static $instance = null;
        return $instance ?? $instance = new self();
    }

    /**
     * @param string $instance
     * @return bool
     * @throws \Difra\Exception
     */
    public function view(string $instance): bool
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

    /**
     * @return bool
     */
    public function isPrintable(): bool
    {
        return true;
    }
}