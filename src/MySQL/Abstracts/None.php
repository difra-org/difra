<?php

declare(strict_types=1);

namespace Difra\MySQL\Abstracts;

/**
 * Class None
 * @package Difra\MySQL
 */
class None extends Common
{
    /**
     * Initiate database connection
     * @throws \Exception
     */
    protected function realConnect(): void
    {
        $this->connected = false;
    }

    /**
     * realQuery stub
     * @param string $query
     * @return void
     */
    protected function realQuery(string $query): void
    {
    }

    /**
     * realFetch stub
     * @param string $query
     * @param bool $replica
     * @return array|null
     */
    protected function realFetch(string $query, bool $replica = false): ?array
    {
        return [];
    }

    /**
     * getAffectedRows stub
     * @return int
     */
    public function getAffectedRows(): int
    {
        return 0;
    }

    /**
     * getLastId stub
     * @return int
     */
    public function getLastId(): int
    {
        return 0;
    }

    /**
     * realEscape stub
     * @param $string
     * @return string
     */
    protected function realEscape($string): string
    {
        return $string;
    }
}
