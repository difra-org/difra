<?php

namespace Difra\Param;

/**
 * Class AjaxSafeHTML
 * @package Difra\Param
 */
class AjaxSafeHTML extends Common
{
    const source = 'ajax';
    const type = 'html';
    const named = true;
    const filtered = true;
    use Traits\HTML;

    public function __construct($value = '')
    {
        $this->raw = $value;
        $this->value = Filters\HTML::getInstance()->process($value, self::filtered);
    }
}
