<?php

namespace Difra\MySQL\Abstracts;

/**
 * Class None
 * @package Difra\MySQL
 */
class None extends Common
{
    protected function realConnect()
    {
        $this->connected = false;
    }

    /**
     * realQuery stub
     * @param string $query
     */
    protected function realQuery($query)
    {
    }

    /**
     * realFetch stub
     * @param string $query
     * @param bool $replica
     * @return array|null
     */
    protected function realFetch($query, $replica = false)
    {
        return null;
    }

    /**
     * getAffectedRows stub
     * @return int
     */
    protected function getAffectedRows()
    {
        return 0;
    }

    /**
     * getLastId stub
     * @return int
     */
    protected function getLastId()
    {
        return 0;
    }

    /**
     * realEscape stub
     * @param $string
     * @return string
     */
    protected function realEscape($string)
    {
        return $string;
    }
}
