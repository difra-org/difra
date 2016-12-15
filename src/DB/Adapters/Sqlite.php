<?php

namespace Difra\DB\Adapters;

/**
 * Sqlite Adapter
 * Class Sqlite
 * @package Difra\DB
 */
class Sqlite extends Common
{
    /**
     * @inherit
     */
    public static function isAvailable()
    {
        return extension_loaded('pdo_sqlite');
    }

    /**
     * @inherit
     */
    protected function getConnectionString()
    {
        return
            "sqlite:{$this->config['file']}";
    }
}
