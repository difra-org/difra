<?php

namespace Difra\Param;

/**
 * Class AjaxCheckbox
 * @package Difra\Param
 */
class AjaxCheckbox extends Common
{
    const source = 'ajax';
    const type = 'string';
    const named = true;
    const auto = true;

    public function __construct($value = '')
    {
        $this->value = $value ? true : false;
    }
}
