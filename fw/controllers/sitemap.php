<?php

class SitemapController extends \Difra\Controller {

	const CACHE_TTL = 10800; // 3 hours

	/**
	 * Из nginx происходит реврайт сюда с адресов:
	 * /sitemap.xml
	 * /sitemap-1.xml
	 * /sitemap-2.xml и т.д.
	 *
	 * @param Difra\Param\AnyInt $page
	 */
	public function indexAction( \Difra\Param\AnyInt $page = null ) {

		$this->outputType = 'text/xml';
		$this->cache      = self::CACHE_TTL;
		if( !$page ) {
			$this->output = \Difra\Libs\XML\Sitemap::getXML();
		} else {
			$res = \Difra\Libs\XML\Sitemap::getXML( $page->val() );
			if( !$res ) {
				$this->view->httpError( 404, $this->cache );
			}
			$this->output = $res;
		}
	}
}