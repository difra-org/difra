<?php

namespace Difra\Libs\XML;

use Difra\Cache;
use Difra\Envi;
use Difra\Plugger;
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
    const PERPAGE = 150;

    /**
     * Collect sitemap data from plugins
     * @return array
     */
    public static function getSitemap()
    {
        $sitemap = [];
        $plugins = Plugger::getAllPlugins();
        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                if ($plugin->isEnabled()) {
                    if ($sm = $plugin->getSitemap()) {
                        $sitemap = array_merge($sitemap, $sm);
                    }
                }
            }
        }
        if (file_exists($sitemapPHP = DIR_ROOT . '/lib/sitemap.php')) {
            try {
                /** @noinspection PhpIncludeInspection */
                $sitemapData = include($sitemapPHP);
                if (!empty($sitemapData) and $sitemapData !== 1) {
                    $sitemap = array_merge($sitemap, $sitemapData);
                }
            } catch (\Exception $e) {
            }
        }
//        foreach ($sitemap as &$rec) {
//            if ($rec['loc']{0} == '/') {
//                $rec['loc'] = Envi::getURLPrefix(true) . $rec['loc'];
//            }
//        }
        return $sitemap;
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
        $sitemap = self::getSitemap();
        $res = false;
        $pagesNum = floor((sizeof($sitemap) - 1) / self::PERPAGE) + 1;
        $cache->put('sitemap_pages', $pagesNum);

        // When sitemap data fits one sitemap.xml file, it's one page
        if ($autoIndex and sizeof($sitemap) <= self::PERPAGE) {
            $xml = self::makeSitemapXML($sitemap);
            $cache->put('sitemap_index', $xml);
            if ($page) {
                return false;
            }
            return $xml;
        }

        // More than one sitemap page
        $indexXML = self::makeIndexXML(floor((sizeof($sitemap) - 1) / self::PERPAGE) + 1);
        $cache->put('sitemap_index', $indexXML);
        if (is_null($page)) {
            $res = $indexXML;
        }
        for ($pageN = 1; $pageN <= $pagesNum; $pageN++) {
            $urls = array_slice($sitemap, ($pageN - 1) * self::PERPAGE, self::PERPAGE);
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
            foreach ($urls as $url) {
                $urlNode = $smiNode->appendChild($indexXML->createElement('url'));
                foreach ($url as $k => $v) {
                    $v = explode('/', $v);
                    $p = $v[0];
                    $v = array_map('urlencode', $v);
                    $v[0] = $p;
                    $v = implode('/', $v);
                    if ($k == 'loc' and $v{0} == '/') {
                        $v = Envi::getURLPrefix(true) . $v;
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
