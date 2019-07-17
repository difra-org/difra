<?php

namespace Controller;

class Sitemap extends \Difra\Controller
{
    /** Cache lifetime */
    const CACHE_TTL = 900; // 15 minutes

    /**
     * This action handles rewrites from nginx for URLs like:
     * /sitemap.xml
     * /sitemap-1.xml
     * /sitemap-2.xml
     * etc.
     *
     * @throws \Difra\View\HttpError
     */
    public function indexAction(\Difra\Param\AnyInt $page = null)
    {
        $this->cache = self::CACHE_TTL;
        if (!$page) {
            $this->outputType = 'text/xml';
            $this->output = \Difra\Tools\Sitemap::getXML();
        } else {
            $res = \Difra\Tools\Sitemap::getXML($page->val());
            if (!$res) {
                throw new \Difra\View\HttpError(404);
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
     * @throws \Difra\View\HttpError
     * @throws \Difra\Exception
     */
    public function htmlAction(\Difra\Param\AnyInt $page = null)
    {
        $this->cache = self::CACHE_TTL;
        if (!$html = \Difra\Tools\Sitemap::getHTML($page)) {
            throw new \Difra\View\HttpError(404);
        }
        $this->outputType = 'text/html';
        $this->output = $html;
    }
}
