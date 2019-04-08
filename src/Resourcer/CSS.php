<?php

namespace Difra\Resourcer;

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

    protected function __construct()
    {
        if (\Difra\Debugger::isEnabled()) {
            $this->printSequenceDebug = true;
        }
    }

    public function processText($text)
    {
        return \Difra\Libs\Less::compile($text);
    }
}
