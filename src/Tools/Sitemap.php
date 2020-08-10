<?php

namespace Difra\Tools;

use Difra\Cache;
use Difra\Envi;
use Difra\Events\Event;
use Difra\View;

/**
 * Class Sitemap
 * @package Difra\Libs\XML
 */
class Sitemap
{
    /** xml namespace */
    const NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    /** links per page */
    const PERPAGE = 500;

    const EVENT_NAME = 'sitemap';

    /** @var array Sitemap data */
    private static $sitemap = [];

    /**
     * Init sitemap gathering
     */
    public static function init()
    {
        static $done = false;
        if (!$done) {
            $done = true;
            Event::getInstance(self::EVENT_NAME)->trigger();
        }
    }

    /**
     * Add data to sitemap
     * @param $data
     */
    public static function add($data)
    {
        if (!empty($data)) {
            self::$sitemap = array_merge(self::$sitemap, $data);
        }
    }

    /**
     * Get sitemap.xml
     * Null page means index for sitemap pages
     * @param int|null $page
     * @param bool $autoIndex
     * @return bool|string
     */
    public static function getXML($page = null, $autoIndex = true)
    {
        // Get cached data
        $cache = Cache::getInstance();
        if (is_null($page)) {
            if ($res = $cache->get('sitemap_index')) {
                return $res;
            }
        } else {
            if ($res = $cache->get('sitemap_' . $page)) {
                return $res;
            }
            if ($pages = $cache->get('sitemap_pages')) {
                if (($autoIndex and $pages == 1) or $pages < $page) {
                    return false;
                }
            }
        }

        // Get sitemap data
        self::init();
        $res = false;
        $pagesNum = floor((sizeof(self::$sitemap) - 1) / self::PERPAGE) + 1;
        $cache->put('sitemap_pages', $pagesNum);

        // When sitemap data fits one sitemap.xml file, it's one page
        if ($autoIndex and sizeof(self::$sitemap) <= self::PERPAGE) {
            $xml = self::makeSitemapXML(self::$sitemap);
            $cache->put('sitemap_index', $xml);
            if ($page) {
                return false;
            }
            return $xml;
        }

        // More than one sitemap page
        $indexXML = self::makeIndexXML(floor((sizeof(self::$sitemap) - 1) / self::PERPAGE) + 1);
        $cache->put('sitemap_index', $indexXML);
        if (is_null($page)) {
            $res = $indexXML;
        }
        for ($pageN = 1; $pageN <= $pagesNum; $pageN++) {
            $urls = array_slice(self::$sitemap, ($pageN - 1) * self::PERPAGE, self::PERPAGE);
            $xml = self::makeSitemapXML($urls);
            $cache->put('sitemap_' . $pageN, $xml);
            if ($page == $pageN) {
                $res = $xml;
            }
        }
        return $res;
    }

    /**
     * Create index sitemap.xml
     * @param int $pages
     * @return string
     */
    private static function makeIndexXML($pages)
    {
        $indexXML = new \DOMDocument;
        $smiNode = $indexXML->appendChild($indexXML->createElementNS(self::NS, 'sitemapindex'));
        $urlPref = Envi::getURLPrefix();
        for ($i = 1; $i <= $pages; $i++) {
            $smNode = $smiNode->appendChild($indexXML->createElement('sitemap'));
            $smNode->appendChild($indexXML->createElement('loc', "$urlPref/sitemap-" . $i . '.xml'));
        }
        return $indexXML->saveXML();
    }

    /**
     * Create sitemap page
     * @param array $urls
     * @return string
     */
    private static function makeSitemapXML(&$urls)
    {
        $indexXML = new \DOMDocument;
        $smiNode = $indexXML->appendChild($indexXML->createElementNS(self::NS, 'urlset'));
        if (!empty($urls)) {
            $urlPrefix = Envi::getURLPrefix(true);
            foreach ($urls as $url) {
                $urlNode = $smiNode->appendChild($indexXML->createElement('url'));
                foreach ($url as $k => $v) {
                    $v = explode('/', $v);
                    $p = $v[0];
//                    $v = array_map('urlencode', $v);
                    $v[0] = $p;
                    $v = implode('/', $v);
                    if ($k == 'loc') {
                        $v = str_replace('&', '&amp;', $v);
                        if($v{0} == '/') {
                            $v = $urlPrefix . $v;
                        }
                    }
                    $urlNode->appendChild($indexXML->createElement($k, $v));
                }
            }
        }
        return $indexXML->saveXML();
    }

    /**
     * Get sitemap.html
     * @param int|null $page
     * @return bool|null|string
     * @throws \Difra\Exception
     */
    public static function getHTML($page = null)
    {
        if ($html = Cache::getInstance()->get('sitemap-html-' . ($page ?: '0'))) {
            return $html;
        }
        if (!$page) {
            $xml = self::getXML(null, false);
        } else {
            $xml = self::getXML((string)$page, false);
        }
        if (!$xml) {
            return false;
        }
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $html = View::render($dom, 'sitemap', true, true);
        Cache::getInstance()->put('sitemap-html-' . ($page ?: '0'), $html);
        return $html;
    }

    /**
     * Get output XML index for sitemap.html pages
     * @return bool|null|string
     */
    public static function getXMLforHTML()
    {
        if ($html = Cache::getInstance()->get('sitemap-short')) {
            return $html;
        }
        $xml = self::getXML(null, false);
        if (!$xml) {
            return false;
        }
        $sxml = new \SimpleXMLElement($xml);
        $sxml->registerXPathNamespace('sitemap', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $html = '';
        $i = 1;
        foreach ($sxml->xpath('/sitemap:sitemapindex/sitemap:sitemap/sitemap:loc') as $loc) {
            $link = preg_replace('/\.xml$/', '.html', (string)$loc);
            $html .= '<a href="' . $link . '">Sitemap page ' . $i . '</a>';
            $i++;
        }
        Cache::getInstance()->put('sitemap-short', $html);
        return $html;
    }
}
