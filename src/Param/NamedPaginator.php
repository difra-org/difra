<?php

declare(strict_types=1);

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

    /**
     * Constructor
     * @param string $value
     * @throws \Difra\Exception
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
        return call_user_func_array([$this->value, $method], $args);
    }

    /**
     * @return Paginator
     */
    public function val(): Paginator
    {
        return $this->value;
    }
}
