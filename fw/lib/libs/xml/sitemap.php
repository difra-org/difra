<?php

namespace Difra\Libs\XML;

/**
 * Class Sitemap
 *
 * @package Difra\Libs\XML
 */
class Sitemap {

	/** xml namespace */
	const NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
	/** links per page */
	const PERPAGE = 150;

	/**
	 * Собирает данные для Sitemap из плагинов
	 *
	 * @return array
	 */
	public static function getSitemap() {

		$sitemap = array();
		$plugins = \Difra\Plugger::getAllPlugins();
		if( !empty( $plugins ) ) {
			foreach( $plugins as $plugin ) {
				if( $plugin->isEnabled() ) {
					if( $sm = $plugin->getSitemap() ) {
						$sitemap = array_merge( $sitemap, $sm );
					}
				}
			}
		}
		return $sitemap;
	}

	/**
	 * Возвращает sitemap.xml
	 * Если не задан параметр $page, будет возвращен индексный sitemap.xml
	 *
	 * @param int|null $page
	 * @param bool     $autoIndex
	 *
	 * @return bool|string
	 */
	public static function getXML( $page = null, $autoIndex = true ) {

		// Получаем данные из кэша
		$cache = \Difra\Cache::getInstance();
		if( is_null( $page ) ) {
			if( $res = $cache->get( 'sitemap_index' ) ) {
				return $res;
			}
		} else {
			if( $res = $cache->get( 'sitemap_' . $page ) ) {
				return $res;
			}
			if( $pages = $cache->get( 'sitemap_pages' ) ) {
				if( ( $autoIndex and $pages == 1 ) or $pages < $page ) {
					return false;
				}
			}
		}
		// Собираем новый sitemap
		$sitemap = self::getSitemap();
		// Если кэш отключен, возвращаем результат
		if( $cache->adapter == 'None' ) {
			if( is_null( $page ) ) {
				if( $autoIndex and sizeof( $sitemap ) <= self::PERPAGE ) {
					return self::makeSitemapXML( $sitemap );
				} else {
					return self::makeIndexXML( floor( ( sizeof( $sitemap ) - 1 ) / self::PERPAGE ) + 1 );
				}
			} else {
				if( $autoIndex and sizeof( $sitemap ) <= self::PERPAGE ) {
					return false;
				}
				$urls = array_slice( $sitemap, ( $page - 1 ) * self::PERPAGE, self::PERPAGE );
				if( empty( $urls ) ) {
					return false;
				}
				return self::makeSitemapXML( $urls );
			}
		}
		// Разбираем данные, обновляем кэш
		$res = false;
		$pagesNum = floor( ( sizeof( $sitemap ) - 1 ) / self::PERPAGE ) + 1;
		$cache->put( 'sitemap_pages', $pagesNum );
		// Получается одна страница
		if( $autoIndex and sizeof( $sitemap ) <= self::PERPAGE ) {
			$xml = self::makeSitemapXML( $sitemap );
			$cache->put( 'sitemap_index', $xml );
			if( $page ) {
				return false;
			}
			return $xml;
		}
		// Получается несколько страниц
		$xml = self::makeIndexXML( floor( ( sizeof( $sitemap ) - 1 ) / self::PERPAGE ) + 1 );
		$cache->put( 'sitemap_index', $xml );
		if( is_null( $page ) ) {
			$res = $xml;
		}
		for( $pageN = 1; $pageN <= $pagesNum; $pageN++ ) {
			$urls = array_slice( $sitemap, ( $pageN - 1 ) * self::PERPAGE, self::PERPAGE );
			$xml = self::makeSitemapXML( $urls );
			$cache->put( 'sitemap_' . $pageN, $xml );
			if( $page == $pageN ) {
				$res = $xml;
			}
		}
		return $res;
	}

	/**
	 * Формирует индексный sitemap.xml
	 *
	 * @param int $pages
	 *
	 * @return string
	 */
	private static function makeIndexXML( $pages ) {

		$indexXML = new \DOMDocument;
		$smiNode = $indexXML->appendChild( $indexXML->createElementNS( self::NS, 'sitemapindex' ) );
		$urlPref = 'http://' . \Difra\Envi::getHost();
		for( $i = 1; $i <= $pages; $i++ ) {
			$smNode = $smiNode->appendChild( $indexXML->createElement( 'sitemap' ) );
			$smNode->appendChild( $indexXML->createElement( 'loc', "$urlPref/sitemap-" . $i . '.xml' ) );
		}
		return $indexXML->saveXML();
	}

	/**
	 * Формирует sitemap.xml со ссылками
	 *
	 * @param array $urls
	 *
	 * @return string
	 */
	private static function makeSitemapXML( &$urls ) {

		$indexXML = new \DOMDocument;
		$smiNode = $indexXML->appendChild( $indexXML->createElementNS( self::NS, 'urlset' ) );
		if( !empty( $urls ) ) {
			foreach( $urls as $url ) {
				$urlNode = $smiNode->appendChild( $indexXML->createElement( 'url' ) );
				foreach( $url as $k => $v ) {
					$v = explode( '/', $v );
					$p = $v[0];
					$v = array_map( 'urlencode', $v );
					$v[0] = $p;
					$v = implode( '/', $v );
					$urlNode->appendChild( $indexXML->createElement( $k, $v ) );
				}
			}
		}
		return $indexXML->saveXML();
	}

	public static function getHTML( $page = null ) {

		if( $html = \Difra\Cache::getInstance()->get( 'sitemap-html-' . ( $page ? : '0' ) ) ) {
			return $html;
		}
		if( !$page ) {
			$xml = \Difra\Libs\XML\Sitemap::getXML( null, false );
		} else {
			$xml = \Difra\Libs\XML\Sitemap::getXML( (string)$page, false );
		}
		if( !$xml ) {
			return false;
		}
		$dom = new \DOMDocument();
		$dom->loadXML( $xml );
		$html = \Difra\View::render( $dom, 'sitemap', true, true );
		\Difra\Cache::getInstance()->put( 'sitemap-html-' . ( $page ? : '0' ), $html );
		return $html;
	}

	public static function getXMLforHTML() {

		if( $html = \Difra\Cache::getInstance()->get( 'sitemap-short' ) ) {
			return $html;
		}
		$xml = \Difra\Libs\XML\Sitemap::getXML( null, false );
		if( !$xml ) {
			return false;
		}
		$sxml = new \SimpleXMLElement( $xml );
		$sxml->registerXPathNamespace( 'sitemap', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		$html = '';
		$i = 1;
		foreach( $sxml->xpath( '/sitemap:sitemapindex/sitemap:sitemap/sitemap:loc' ) as $loc ) {
			$link = preg_replace( '/\.xml$/', '.html', (string)$loc );
			$html .= '<a href="' . $link . '">Sitemap page ' . $i . '</a>';
			$i++;
		}
		\Difra\Cache::getInstance()->put( 'sitemap-short', $html );
		return $html;
	}
}
