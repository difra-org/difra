<?php

declare(strict_types=1);

namespace Difra\Resourcer;

/**
 * Class JS
 * @package Difra\Resourcer
 */
class JS extends Abstracts\Plain
{
    protected ?string $type = 'js';
    protected bool $printable = true;
    protected ?string $contentType = 'application/x-javascript';
    protected bool $instancesOrdered = true;

    protected function __construct()
    {
        parent::__construct();
        if (\Difra\Debugger::isEnabled()) {
            $this->showSequence = true;
        }
    }
}
