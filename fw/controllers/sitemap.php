<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Class SitemapController
 */
class SitemapController extends \Difra\Controller {

	/** Cache lifetime */
	const CACHE_TTL = 10800; // 3 hours

	/**
	 * Из nginx происходит реврайт сюда с адресов:
	 * /sitemap.xml
	 * /sitemap-1.xml
	 * /sitemap-2.xml и т.д.
	 *
	 * @param Difra\Param\AnyInt $page
	 *
	 * @throws Difra\View\Exception
	 */
	public function indexAction( \Difra\Param\AnyInt $page = null ) {

		$this->cache = self::CACHE_TTL;
		if( !$page ) {
			$this->outputType = 'text/xml';
			$this->output = \Difra\Libs\XML\Sitemap::getXML();
		} else {
			$res = \Difra\Libs\XML\Sitemap::getXML( $page->val() );
			if( !$res ) {
				throw new \Difra\View\Exception( 404 );
			}
			$this->outputType = 'text/xml';
			$this->output = $res;
		}
	}

	public function htmlAction( \Difra\Param\AnyInt $page = null ) {

		$this->cache = self::CACHE_TTL;
		if( !$html = \Difra\Libs\XML\Sitemap::getHTML( $page ) ) {
			throw new \Difra\View\Exception( 404 );
		}
		$this->outputType = 'text/html';
		$this->output = $html;
	}
}