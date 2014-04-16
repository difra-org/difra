<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Adm;

use Difra\Envi\Action;

/**
 * Class Localemanage
 *
 * @package Difra\Adm
 */
class Localemanage {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function getLocalesList() {

		return \Difra\Resourcer\Locale::getInstance()->findInstances();
	}

	public function getLocale( $locale ) {

		return \Difra\Resourcer::getInstance( 'locale' )->compile( $locale, true );
	}

	public function getLocalesTree() {

		$instances = $this->getLocalesList();
		$locales = array( 'xpaths' => array() );
		foreach( $instances as $instance ) {
			$xml = new \DOMDocument();
			$xml->loadXML( $this->getLocale( $instance ) );
			$locales[$instance] = array();
			$this->xml2tree( $xml->documentElement, $locales[$instance], $locales['xpaths'], '' );
		}
		return $locales;
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getLocalesTreeXML( $node ) {

		$tree = $this->getLocalesTree();
		foreach( $tree as $loc => $data ) {
			switch( $loc ) {
			case 'xpaths':
				break;
			default:
				/** @var \DOMElement $localeNode */
				$localeNode = $node->appendChild( $node->ownerDocument->createElement( 'locale' ) );
				$localeNode->setAttribute( 'name', $loc );
				foreach( $data as $module => $data2 ) {
					/** @var \DOMElement $moduleNode */
					$moduleNode = $localeNode->appendChild( $localeNode->ownerDocument->createElement( 'module' ) );
					$moduleNode->setAttribute( 'name', $module );
					foreach( $data2 as $k => $v ) {
						/** @var \DOMElement $strNode */
						$strNode = $moduleNode->appendChild( $moduleNode->ownerDocument->createElement( 'item' ) );
						$strNode->setAttribute( 'xpath', $k );
						$strNode->setAttribute( 'missing', 0 );
						foreach( $v as $k2 => $v2 ) {
							$strNode->setAttribute( $k2, $v2 );
						}
					}
					// missed strings
					foreach( $tree['xpaths'][$module] as $k => $v ) {
						if( isset( $data2[$k] ) ) {
							continue;
						}
						$strNode = $moduleNode->appendChild( $moduleNode->ownerDocument->createElement( 'item' ) );
						$strNode->setAttribute( 'xpath', $k );
						$strNode->setAttribute( 'missing', 1 );
					}
				}
				// missed modules
				foreach( $tree['xpaths'] as $module => $data2 ) {
					if( isset( $data[$module] ) ) {
						continue;
					}
					$moduleNode = $localeNode->appendChild( $localeNode->ownerDocument->createElement( 'module' ) );
					$moduleNode->setAttribute( 'name', $module );
					foreach( $data2 as $k => $v ) {
						$strNode = $moduleNode->appendChild( $moduleNode->ownerDocument->createElement( 'item' ) );
						$strNode->setAttribute( 'xpath', $k );
						$strNode->setAttribute( 'missing', 1 );
						$strNode->setAttribute( 'source', basename( $v ) );
					}
				}
			}
		}
	}

	/**
	 * @param \DOMElement|\DOMNode $node
	 * @param array                $arr
	 * @param array                $allxpaths
	 * @param string               $xpath
	 */
	public function xml2tree( $node, &$arr, &$allxpaths, $xpath ) {

		foreach( $node->childNodes as $item ) {

			switch( $item->nodeType ) {
			case XML_ELEMENT_NODE:
				$this->xml2tree( $item, $arr, $allxpaths, ( $xpath ? $xpath . '/' : '' ) . $item->nodeName );
				break;
			case XML_TEXT_NODE:
				$source = $node->getAttribute( 'source' );
				$module = $this->getModule( $source );
				if( !isset( $arr[$module] ) ) {
					$arr[$module] = array();
				}
				$arr[$module][$xpath] = array(
					'source' => basename( $source ),
					'text' => $item->nodeValue,
					'usage' => ( $usage = $this->findUsages( $xpath ) )
				);
				if( $usage ) {
					if( !isset( $allxpaths[$module] ) ) {
						$allxpaths[$module] = array();
					}
					$allxpaths[$module][$xpath] = $source;
				}
				break;
			}
		}
	}

	public function getModule( $filename ) {

		if( strpos( $filename, DIR_PLUGINS ) === 0 ) {
			$res = substr( $filename, strlen( DIR_PLUGINS ) );
			$res = trim( $res, '/' );
			$res = explode( '/', $res, 2 );
			return 'plugins/' . $res[0];
		} elseif( strpos( $filename, DIR_FW ) === 0 ) {
			return 'fw';
		} elseif( strpos( $filename, DIR_SITE ) === 0 ) {
			return 'site';
		} elseif( strpos( $filename, DIR_ROOT . 'locale' ) === 0 ) {
			return '/';
		} else {
			return 'unknown';
		}
	}

	public function findUsages( $xpath ) {

		static $cache = array();
		if( isset( $cache[$xpath] ) ) {
			return $cache[$xpath];
		}
		static $templates = null;
		if( is_null( $templates ) ) {
			$resourcer = \Difra\Resourcer::getInstance( 'xslt' );
			$types = $resourcer->findInstances();
			foreach( $types as $type ) {
				$templates[$type] = $resourcer->compile( $type );
			}
		}
		$matches = 0;
		foreach( $templates as $tpl ) {
			$matches += substr_count( $tpl, '"$locale/' . $xpath . '"' );
			$matches += substr_count( $tpl, '{$locale/' . $xpath . '}' );
		}
		static $menus = null;
		if( is_null( $menus ) ) {
			$resourcer = \Difra\Resourcer::getInstance( 'menu' );
			$types = $resourcer->findInstances();
			foreach( $types as $type ) {
				$menus[$type] = $resourcer->compile( $type );
			}
		}
		foreach( $menus as $tpl ) {
			$matches += substr_count( $tpl, 'xpath="locale/' . $xpath . '"' );
		}
		static $controllers = null;
		if( is_null( $controllers ) ) {
			$controllers = array();
			$dirs = Action::getControllerPaths();
			foreach( $dirs as $dir ) {
				$this->getAllFiles( $controllers, $dir );
				$this->getAllFiles( $controllers, $dir . '../lib' );
			}
		}
		foreach( $controllers as $controller ) {
			$matches += substr_count( $controller, "'" . $xpath . "'" );
			$matches += substr_count( $controller, '"' . $xpath . '"' );
		}
		return $cache[$xpath] = $matches;
	}

	public function getAllFiles( &$collection, $dir ) {

		if( !is_dir( $dir ) ) {
			return;
		}
		$d = opendir( $dir );
		while( $f = readdir( $d ) ) {
			$df = $dir . '/' . $f;
			if( $f{0} == '.' ) {
				continue;
			}
			if( is_dir( $df ) ) {
				$this->getAllFiles( $collection, $df );
			} else {
				$collection[trim( $df, '/' )] = file_get_contents( $df );
			}
		}
	}

	public static function getLocaleLength( $localeArray ) {

		$difraVersion = 'Difra ' . \Difra\Envi\Version::getBuild();
		$Cache = \Difra\Cache::getInstance();
		$currentLocaleDate = date( 'Y-m-d', time() );

		// проверяем наличие кэша
		$fl = $Cache->get( 'difraLocales' );

		if( !is_null( $fl ) ) {
			$nt = unserialize( base64_decode( $fl ) );
			$localeString = unserialize( $nt['locale'] );
			if( self::checkLocaleExpired( $localeString ) ) {
				$nt['cached'] = true;
				return $nt;
			}
		}

		// файловый кэш
		$flName = base64_encode( 'difra_license_file.lic' );
		if( file_exists( DIR_DATA . $flName ) ) {
			$lFile = file_get_contents( DIR_DATA . $flName);
			if( $lFile!='' ) {
				$nt = unserialize( base64_decode( $lFile ) );
				$localeString = unserialize( $nt['locale'] );
				if( self::checkLocaleExpired( $localeString ) ) {
					$nt['cached'] = true;
					return $nt;
				}
			}
		}

		$headerArray = array(
			base64_decode( 'Q2FjaGUtQ29ucnRvbDogbm8tY2FjaGU=' ),
			base64_decode( 'UHJhZ21hOiBuby1jYWNoZQ==' )
		);

		$Cache->put( 'difraCurrentLocaleDate', convert_uuencode( $currentLocaleDate ) );
		file_put_contents( DIR_DATA . base64_encode( 'localeCacheDate' ), convert_uuencode( $currentLocaleDate ) );

		$postData = serialize( $localeArray );
		$postFields = array( 'data' => base64_encode( $postData ) );
		$curla = curl_init();
		curl_setopt( $curla, CURLOPT_URL, base64_decode( 'aHR0cDovL2RybS5wbmQuZGV2LmphbQ==' ) );
		curl_setopt( $curla, CURLOPT_POST, 1 );
		curl_setopt( $curla, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curla, CURLOPT_USERAGENT, $difraVersion );
		curl_setopt( $curla, CURLOPT_HTTPHEADER, $headerArray );
		curl_setopt( $curla, CURLOPT_POSTFIELDS, $postFields );
		curl_setopt( $curla, CURLOPT_TIMEOUT, 3 );
		curl_exec( $curla );
		$res = curl_multi_getcontent( $curla );
		$httpCode = curl_getinfo( $curla, CURLINFO_HTTP_CODE );
		curl_close( $curla );
		if( $httpCode != 200 ) {
			self::exitLocale();
		}

		$localePem = \Difra\Libs\Security\Publickey::get();
		openssl_get_privatekey( $localePem );

		$encodedRes = base64_decode( $res );

		$encodedArray = unserialize( $encodedRes );
		$vr = openssl_verify( $encodedArray['license'], $encodedArray['signature'], $localePem, 'sha256WithRSAEncryption' );

		if( $vr != 1  ) {
			self::exitLocale();
		}

		return array( 'locale' => $encodedArray['license'], 'localeString' => $encodedArray['signature'] );
	}

	public static function exitLocale() {
		header( base64_decode( 'SFRUUC8xLjAgNTAzIFNlcnZpY2UgVW5hdmFpbGFibGU=' ) );
		echo base64_decode( 'PGh0bWw+PGhlYWQ+PHRpdGxlPkVycm9yIDUwMzwvdGl0bGU+PC9oZWFkPjxib2R5PjxjZW50ZXI+PGgxPjUwMyBTZXJ2aWNlIFVuYXZhaWxhYmxlPC9oMT5Tb2Z0d2FyZSBsaWNlbnNlIGlzIGludmFsaWQuPC9jZW50ZXI+PC9ib2R5PjwvaHRtbD4=' );
		exit();
	}

	public static function checkLocaleExpired( $localeArray ) {

		$Cache = \Difra\Cache::getInstance();

		$cld = $Cache->get( 'difraCurrentLocaleDate' );

		if( is_null( $cld ) ) {
			$cld = @file_get_contents( DIR_DATA . base64_encode( 'localeCacheDate' ) );
		}

		if( convert_uudecode( $cld ) == date( 'Y-m-d', time() ) ) {
			return true;
		}

		if( isset( $localeArray['expired'] ) && $localeArray['expired'] !='' ) {
			$expiredValue = strtotime( $localeArray['expired'] . ' 00:00:00' );
			if( $expiredValue < time() ) {
				return false;
			}
		}
		return true;
	}

	public static function checkStatus( $localeArray ) {

		if( !isset( $localeArray['status'] ) || $localeArray['status'] != 'ok' ) {
			self::exitLocale();
		}
	}
}