<?php

/**
 * Class SitemapController
 * Displays site maps.
 * Plugins should export data to appear in site maps.
 */
class SitemapController extends \Difra\Controller
{
    /** Cache lifetime */
    const CACHE_TTL = 10800; // 3 hours

    /**
     * This action handles rewrites from nginx for URLs like:
     * /sitemap.xml
     * /sitemap-1.xml
     * /sitemap-2.xml
     * etc.
     *
     * @param Difra\Param\AnyInt $page
     * @throws Difra\View\Exception
     */
    public function indexAction(\Difra\Param\AnyInt $page = null)
    {
        $this->cache = self::CACHE_TTL;
        if (!$page) {
            $this->outputType = 'text/xml';
            $this->output = \Difra\Libs\XML\Sitemap::getXML();
        } else {
            $res = \Difra\Libs\XML\Sitemap::getXML($page->val());
            if (!$res) {
                throw new \Difra\View\Exception(404);
            }
            $this->outputType = 'text/xml';
            $this->output = $res;
        }
    }

    /**
     * This action handles rewrites from nginx for URLs like:
     * /sitemap.html
     * /sitemap-1.html
     * /sitemap-2.html
     * etc.
     *
     * @param \Difra\Param\AnyInt $page
     * @throws \Difra\View\Exception
     */
    public function htmlAction(\Difra\Param\AnyInt $page = null)
    {
        $this->cache = self::CACHE_TTL;
        if (!$html = \Difra\Libs\XML\Sitemap::getHTML($page)) {
            throw new \Difra\View\Exception(404);
        }
        $this->outputType = 'text/html';
        $this->output = $html;
    }
}
