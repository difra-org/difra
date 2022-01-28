<?php

declare(strict_types=1);

namespace Difra\Libs;

use Difra\Debugger;

/**
 * Class Less
 * LESS wrapper.
 * @package Difra\Libs
 */
class Less
{
    public const VERSION = \Less_Version::version;
    /**
     * Init Less.php library
     * @throws \Exception
     */
    public static function init()
    {
//        static $done = false;
//        if ($done)
//            return;
//        include(__DIR__ . '/Less/src/Autoloader.php');
//        \Less_Autoloader::register();
//        $done = true;
    }

    /**
     * Convert LESS to CSS
     * @param string $string
     * @return string
     * @throws \Exception
     */
    public static function compile(string $string): string
    {
        self::init();
        $parser = new \Less_Parser();
        $parser->SetOptions([
            'compress' => !Debugger::isEnabled()
        ]);
        $parser->parse($string);
        return $parser->getCss();
    }
}
