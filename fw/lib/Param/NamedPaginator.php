<?php

namespace Difra\Param;
use Difra\Unify\Paginator;

/**
 * Class NamedInt
 * @package Difra\Param
 */
class NamedPaginator extends Common
{
    const source = 'query';
    const type = 'int';
    const named = true;
    const auto = true;

    /** @noinspection PhpMissingParentConstructorInspection
     * Constructor
     * @param string $value
     */
    public function __construct($value = '')
    {
        if ($value === '') {
            $value = 1;
        }
        $this->value = new Paginator();
        $this->value->setPage($value);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->value, $method), $args);
    }

    /**
     * @return Paginator
     */
    public function val()
    {
        return $this->value;
    }
}
