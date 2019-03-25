<?php

namespace Difra\Resourcer;

use Difra\Libs\Less;

/**
 * Class CSS
 * @package Difra\Resourcer
 */
class CSS extends Abstracts\Plain
{
    protected $type = 'css';
    protected $printable = true;
    protected $contentType = 'text/css';
    protected $instancesOrdered = true;

    public function processText($text)
    {
        return Less::compile($text);
    }
}
