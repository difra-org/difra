<?php

namespace Difra\Envi;

use Difra\Config;
use Difra\Envi;

/**
 * Class Setup
 * @package Difra\Envi
 */
class Setup
{
    /**
     * Set up environment
     */
    public static function run()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding(self::getEncoding());
        }
        ini_set('short_open_tag', false);
        ini_set('asp_tags', false);
        ini_set('mysql.trace_mode', false);

        // set session domain
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.cookie_domain', '.' . Envi::getHost(true));

        // set default time zone
        // todo: use date_default_timezone_get()
        if (self::$timeZone = Config::getInstance()->get('timezone')) {
            date_default_timezone_set(self::$timeZone);
        } elseif (self::$timeZone = ini_get('date.timezone')) {
        } else {
            self::$timeZone = 'Europe/Berlin';
            date_default_timezone_set(self::$timeZone);
        }

        self::setLocale();
    }

    /** @var string Default locale */
    static private $locale = false;

    /**
     * Set locale
     * @param $locale
     */
    public static function setLocale($locale = false)
    {
        if (!$locale) {
            if ($configLocale = Config::getInstance()->get('locale')) {
                $locale = $configLocale;
            }
        }
        self::$locale = $locale ?: 'ru_RU';
        setlocale(LC_ALL, [self::$locale . '.' . self::getEncoding(), self::$locale . '.UTF-8', self::$locale . '.utf8']);
        setlocale(LC_NUMERIC, ['en_US.' . self::getEncoding(), 'en_US.UTF-8', 'en_US.utf8']);
    }

    /**
     * Get locale name
     * @return string
     */
    public static function getLocale()
    {
        return self::$locale;
    }

    /** @var string Encoding */
    static private string $encoding = 'UTF-8';

    /**
     * Set encoding
     * @param string $encoding
     */
    public static function setEncoding(string $encoding = 'UTF-8'): void {
        self::$encoding = $encoding;
    }

    /**
     * Get encoding
     * @return string
     */
    public static function getEncoding(): string {
        return self::$encoding;
    }

    /**
     * Get locale language name
     */
    public static function getLocaleLang()
    {
        return substr(self::$locale, 0, 2);
    }

    /** @var string Time zone name */
    private static $timeZone = null;

    /**
     * Get time zone
     * @return string
     */
    public static function getTimeZone()
    {
        return self::$timeZone;
    }

    /**
     * Get time zone as DateTimeZone object
     * @return \DateTimeZone
     */
    public static function getDTZone()
    {
        static $dtz = null;
        return $dtz ?: $dtz = new \DateTimeZone(self::getTimeZone());
    }
}
