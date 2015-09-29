<?php

namespace Difra\PDO\Abstracts;

use Difra\Config;
use Difra\Envi;

/**
 * MySQL Adapter
 * Class MySQL
 * @package Difra\PDO
 */
class MySQL extends Common
{
    /**
     * Is module available?
     * @return bool
     */
    public static function isAvailable()
    {
        return extension_loaded('pdo_mysql');
    }

    /**
     * Реализация установки соединения с базой
     * @throws \PDOException
     */
    protected function realConnect()
    {
        $config = Config::getInstance()->get('db');
        $this->pdo = new \PDO(
            'mysql:host=' . (!empty($config['hostname']) ? $config['hostname'] : '') .
            ';dbname=' . (!empty($config['database']) ? $config['database'] : Envi::getSubsite()) .
            ';charset=utf8',
            !empty($config['username']) ? Envi::getSubsite() : '',
            !empty($config['password']) ? $config['password'] : '',
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );
    }
}
