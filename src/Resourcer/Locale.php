<?php

declare(strict_types=1);

namespace Difra\Resourcer;

/**
 * Class Locale
 * @package Difra\Resourcer
 */
class Locale extends Abstracts\XML
{
    protected ?string $type = 'locale';
    protected bool $printable = false;
}
