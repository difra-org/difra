<?php

namespace Difra\Minify;

/**
 * CSS minification adapter
 * Usage: \Difra\Minify\CSS::getInstance()->minify( $css )
 */
class CSS extends Common
{
    /**
     * Minify CSS
     * @param string $data
     * @return string
     */
    public function minify($data)
    {
        /**
         * Disabled: minification is made by LESS now
         * $data = preg_replace( '/\/\*.*?\*\//s', '', $data ); // remove comments
         * $data = preg_replace( '/\s+/', ' ', $data ); // remove replace multiple whitespaces with space
         * $data = preg_replace( '/\s?([{};:,])\s/', '$1', $data ); // remove spaces near some symbols
         */
        return $data;
    }
}

