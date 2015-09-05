<?php

namespace Difra\Minify;

/**
 * Class JS
 * @package Difra\Minify
 */
class JS extends Common
{
    /**
     * Minify JavaScript
     * @param string $data
     * @return string
     */
    public function minify($data)
    {
        return JS\JSMin::minify($data);
    }
}
	
