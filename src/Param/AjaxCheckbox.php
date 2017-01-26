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
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * @inheritdoc
     */
    public function __construct($value = '')
    {
        $this->value = $value ? true : false;
    }
}
