<?php

declare(strict_types=1);

namespace Difra\Resourcer;

/**
 * Class XSLT
 * @package Difra\Resourcer
 */
class XSLT extends Abstracts\XSLT
{
    protected ?string $type = 'xslt';
    protected bool $printable = false;
}
