<?php

declare(strict_types=1);

namespace Difra;

use Difra\MySQL\Abstracts\MySQLi;
use Difra\MySQL\Abstracts\None;

/**
 * Factory for MySQL
 * Deprecated: please use PDO.
 * Class MySQL
 * @package Difra
 * @deprecated
 */
class MySQL
{
    /** Auto detect adapter */
    public const INST_AUTO = 'auto';
    /** MySQLi */
    public const INST_MYSQLI = 'MySQLi';
    /** Stub */
    public const INST_NONE = 'none';
    /** Default adapter */
    public const INST_DEFAULT = self::INST_AUTO;
    /** @var array Adapters registry */
    private static array $adapters = [];

    /**
     * @param string $adapter
     * @param bool $new
     * @return MySQL\Abstracts\MySQLi|MySQL\Abstracts\None
     */
    public static function getInstance(string $adapter = self::INST_DEFAULT, bool $new = false): None|MySQLi
    {
        if ($adapter == self::INST_AUTO) {
            static $auto = null;
            if (!is_null($auto)) {
                return self::getInstance($auto, $new);
            }

            if (MySQLi::isAvailable()) {
                Debugger::addLine('MySQL module: MySQLi');
                return self::getInstance($auto = self::INST_MYSQLI, $new);
            } else {
                Debugger::addLine('No suitable MySQL module detected');
                return self::getInstance($auto = self::INST_NONE, $new);
            }
        }

        if (!$new and isset(self::$adapters[$adapter])) {
            return self::$adapters[$adapter];
        }

        $obj = match ($adapter) {
            self::INST_MYSQLI => new MySQLi(),
            default => new None(),
        };
        if (!$new) {
            self::$adapters[$adapter] = $obj;
        }
        return $obj;
    }
}
