<?php

namespace Difra;

use Difra\Envi\Setup;

/**
 * Class Locales
 * @package Difra
 */
class Locales
{
    /** @var string Default locale */
    public $locale = 'en_US';
    /**
     * @var \DOMDocument
     */
    public $localeXML = null;
    // TODO: replace this values with locale's built in methods?
    /** @var array Date formats */
    public $dateFormats = ['ru_RU' => 'd.m.y', 'en_US' => 'm-d-y'];
    /** @var array Date and time formats */
    public $dateTimeFormats = ['ru_RU' => 'd.m.y H:i:s', 'en_US' => 'm-d-y h:i:s A'];
    /** @var bool Locale is loaded flag */
    private $loaded = false;

    /**
     * Constructor
     * @param $locale
     */
    private function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Forbid cloning
     */
    private function __clone()
    {
    }

    /**
     * Get text string from current locale (short form)
     * @param $xpath
     * @return bool|string
     * @throws Exception
     */
    public static function get($xpath)
    {
        /** @noinspection PhpDeprecationInspection */
        return @self::getInstance()->getXPath($xpath);
    }

    /**
     * Get locale string by XPath
     * NOT DEPRECATED. Marked as deprecated to get rid of old \Difra\Locales::getInstance()->getXPath( ... ) calls
     * in favor of \Difra\Locales::get( ... ) calls.
     * @param string $xpath
     * @return string|bool
     * @throws Exception
     * @deprecated
     */
    public function getXPath($xpath)
    {
        static $simpleXML = null;
        if (is_null($simpleXML)) {
            $this->load();
            $simpleXML = simplexml_import_dom($this->localeXML);
        }
        $s = $simpleXML->xpath($xpath);
        if (empty($s) and Debugger::isEnabled()) {
            $s = ['No language item for: ' . $xpath];
        }
        return sizeof($s) ? (string)$s[0] : false;
    }

    /**
     * Load locale resource
     * @throws Exception
     */
    private function load()
    {
        if (!$this->loaded) {
            $xml = Resourcer::getInstance('locale')->compile($this->locale);
            $this->localeXML = new \DOMDocument();
            $this->localeXML->loadXML($xml);
        }
    }

    /**
     * Singleton
     * @param null $locale
     * @return Locales
     */
    public static function getInstance($locale = null)
    {
        static $locales = [];
        if (!$locale) {
            $locale = Setup::getLocale();
        }
        if (isset($locales[$locale])) {
            return $locales[$locale];
        }
        $locales[$locale] = new self($locale);
        return $locales[$locale];
    }

    /**
     * Returns locale as XML document
     * @param \DOMElement $node
     * @return void
     * @throws Exception
     */
    public function getLocaleXML($node)
    {
        $this->load();
        if (!is_null($this->localeXML)) {
            $node->appendChild($node->ownerDocument->importNode($this->localeXML->documentElement, true));
        }
    }

    /**
     * Set current locale
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Validate date string
     * @param $string
     * @return bool
     */
    public function isDate($string)
    {
        if (!$date = $this->parseDate($string)) {
            return false;
        }
        return checkdate($date[1], $date[2], $date[0]);
    }

    /**
     * Parse date string
     * Returns array [ 0 => Y, 1 => m, 2 => d ]
     * @param string $string
     * @param string|bool $locale
     * @return array|bool
     */
    public function parseDate($string, $locale = false)
    {
        $string = str_replace(['.', '-'], '/', $string);
        $pt = explode('/', $string);
        if (sizeof($pt) != 3) {
            return false;
        }
        // returns $date[year,month,day] depending on current locale and dateFormats.
        $date = [0, 0, 0];
        $localeInd = ['y' => 0, 'm' => 1, 'd' => 2];
        $df = $this->dateFormats[$locale ?: $this->locale];
        $df = str_replace(['-', '.'], '/', $df);
        $localePt = explode('/', $df);
        foreach ($localePt as $ind => $key) {
            $date[$localeInd[$key]] = $pt[$ind];
        }
        // Get 4-digit year number from 2-digit year number
        if ($date[0] < 100) {
            $date[0] = ($date[0] < 70 ? 2000 : 1900) + $date[0];
        }
        return $date;
    }

    /**
     * Convert local date string to MySQL date string
     * @param string $dateString if ommited, current date is used
     * @return string|false
     */
    public function getMysqlDate($dateString = null)
    {
        if (!$dateString) {
            return date('%Y-%m-%d');
        }
        if (!$date = $this->parseDate($dateString)) {
            return false;
        }
        return implode('-', $date);
    }

    /**
     * Get MySQL syntax for getting localized dates
     * @param bool $locale
     * @return string
     */
    public function getMysqlFormat(bool $locale = false): string
    {
        $localePt = $this->dateFormats[$locale ?: $this->locale];
        return str_replace(['d', 'm', 'y'], ['%d', '%m', '%Y'], $localePt);
    }

    /**
     * Convert MySQL date string to localized date string
     * @param string $date
     * @param boolean $withTime
     * @return string
     */
    public function getDateFromMysql($date, $withTime = false)
    {
        $date = explode(' ', $date);
        $date[0] = explode('-', $date[0]);
        $date[1] = explode(':', $date[1]);

        if ($withTime) {
            return $this->getDateTime(
                mktime($date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0])
            );
        }
        return $this->getDate(mktime($date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0]));
    }

    /**
     * Get localized date and time from timestamp
     * @param $timestamp
     * @return string
     */
    public function getDateTime($timestamp)
    {
        return date($this->dateTimeFormats[$this->locale], $timestamp);
    }

    /**
     * Get localized date from timestamp
     * @param int $timestamp
     * @return string
     */
    public function getDate($timestamp)
    {
        return date($this->dateFormats[$this->locale], $timestamp);
    }

    /**
     * Create link part from string.
     * Used to replace all uncommon characters with dash.
     * @param string $string
     * @return string
     */
    public function makeLink($string)
    {
        $link = '';
        // This string is UTF-8!
        $num = preg_match_all('/[A-Za-zА-Яа-я0-9Ёё]*/u', $string, $matches);
        if ($num and !empty($matches[0])) {
            $matches = array_filter($matches[0], 'strlen');
            $link = implode('-', $matches);
        }
        if ($link == '') {
            $link = '-';
        }
        return mb_strtolower($link);
    }

    /**
     * Format currency
     * @param float $value Money value
     * @param string $currency 3-letter ISO 4217 currency code
     * @return string
     */
    public static function formatCurrency(float $value, string $currency)
    {
        $locales = self::getInstance();
        /** @var \NumberFormatter[] $nFormats */
        static $nFormats = [];
        if (!isset($nFormats[$locales->locale])) {
            $nFormats[$locales->locale] = new \NumberFormatter('ru_RU', \NumberFormatter::CURRENCY);
        }
        return $nFormats[$locales->locale]->formatCurrency($value, $currency);
    }
}
