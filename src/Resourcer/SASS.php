<?php

namespace Difra\Resourcer;

class SASS extends Abstracts\Plain
{
    protected $type = 'scss';
    protected $printable = true;
    protected $contentType = 'text/css';
    protected $instancesOrdered = true;

    protected function __construct()
    {
        if (\Difra\Debugger::isEnabled()) {
            $this->printSequenceDebug = true;
        }
    }

    protected function getFile($file)
    {
        return file_get_contents($file);
    }
}
