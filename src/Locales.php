<?php

declare(strict_types=1);

namespace Difra;

use Difra\Envi\Setup;

use function is_null;
use function simplexml_import_dom;

/**
 * Class Locales
 * @package Difra
 */
class Locales
{
    /** @var static[] */
    protected static array $locales = [];
    /** @var \DOMDocument|null Locale DOM */
    protected ?\DOMDocument $localeXML = null;
    /** @var \SimpleXMLElement|null Locale SimpleXML */
    protected ?\SimpleXMLElement $simpleXML = null;

    /**
     * Repository
     * @param string|null $locale
     * @return Locales
     * @throws \Difra\Exception
     */
    public static function getInstance(?string $locale = null): Locales
    {
        $locale ?: $locale = Setup::getLocale();
        return static::$locales[$locale] ?? static::$locales['locale'] = new static($locale);
    }

    /**
     * @throws \Difra\Exception
     */
    private function __construct(public readonly string $locale)
    {
        $xml = Resourcer::getInstance('locale')->compile($this->locale);
        $this->localeXML = new \DOMDocument();
        $this->localeXML->loadXML($xml);
    }

    private function __clone()
    {
    }

    /**
     * Get text string from the locale
     * @param string $xpath
     * @param string|null $locale
     * @return string|null
     * @throws \Difra\Exception
     */
    public static function get(string $xpath, ?string $locale = null): ?string
    {
        return self::getInstance($locale)->getString($xpath);
    }

    protected function getString(string $xpath): ?string
    {
        static $simpleXML = null;
        if (is_null($simpleXML)) {
            $simpleXML = simplexml_import_dom($this->localeXML);
        }
        $string = $simpleXML->xpath($xpath);
        if (empty($string) and Debugger::isEnabled()) {
            $string = ['No language item for: ' . $xpath];
        }
        return (string)$string[0] ?? null;

    }

    /**
     * Returns locale as XML document
     * @param \DOMElement $node
     * @return void
     */
    public function getLocaleXML(\DOMElement $node)
    {
        $node->appendChild($node->ownerDocument->importNode($this->localeXML->documentElement, true));
    }
}
