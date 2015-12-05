<?php

namespace Difra\DB\Adapters;

/**
 * MySQL Adapter
 * Class MySQL
 * @package Difra\DB
 */
class MySQL extends Common
{
    /**
     * @inherit
     */
    public static function isAvailable()
    {
        return extension_loaded('pdo_mysql');
    }

    /**
     * @inherit
     */
    protected function getConnectionString()
    {
        return
            "{$this->config['type']}:host={$this->config['hostname']};dbname={$this->config['database']};charset=utf8";
    }
}
