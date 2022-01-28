<?php

namespace Difra\Resourcer;

/**
 * Class JS
 * @package Difra\Resourcer
 */
class JS extends Abstracts\Plain
{
    protected $type = 'js';
    protected $printable = true;
    protected $contentType = 'application/x-javascript';
    protected $instancesOrdered = true;

    protected function __construct()
    {
        if (\Difra\Debugger::isEnabled()) {
            $this->showSequence = true;
        }
    }
}
