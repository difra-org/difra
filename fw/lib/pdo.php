<?php

namespace Difra;

use Difra\PDO\Abstracts\MySQL;

/**
 * Factory for PDO
 * Class PDO
 * @package Difra
 */
class PDO
{
    /** Auto detect adapter */
    const INST_AUTO = 'auto';
    /** MySQLi */
    const INST_MYSQL = 'MySQL';
    /** Stub */
    const INST_NONE = 'none';
    const INST_DEFAULT = self::INST_AUTO;
    private static $adapters = [];

    /**
     * @param string $adapter
     * @param bool $new
     * @return MySQL
     * @throws \Difra\Exception
     */
    public static function getInstance($adapter = self::INST_DEFAULT, $new = false)
    {
        if ($adapter == self::INST_AUTO) {
            static $auto = null;
            if (!is_null($auto)) {
                return self::getInstance($auto, $new);
            }

            if (MySQL::isAvailable()) {
                Debugger::addLine("PDO module: MySQL");
                return self::getInstance($auto = self::INST_MYSQL, $new);
            } else {
                throw new Exception('Failed to find PDO adapter');
            }
        }

        if (!$new and isset(self::$adapters[$adapter])) {
            return self::$adapters[$adapter];
        }

        switch ($adapter) {
            case self::INST_MYSQL:
                $obj = new MySQL();
                break;
            default:
                throw new Exception('Failed to find PDO adapter');
        }
        if (!$new) {
            self::$adapters[$adapter] = $obj;
        }
        return $obj;
    }
}
