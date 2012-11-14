<?php

namespace Difra\Adm;

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
		$locales   = array( 'xpaths' => array() );
		foreach( $instances as $instance ) {
			$xml = new \DOMDocument();
			$xml->loadXML( $this->getLocale( $instance ) );
			$locales[$instance] = array();
			$this->xml2tree( $xml->documentElement, $locales[$instance], $locales['xpaths'], '' );
		}
		return $locales;
	}

	public function getLocalesTreeXML( $node ) {

		$tree = $this->getLocalesTree();
		foreach( $tree as $loc => $data ) {
			switch( $loc ) {
			case 'xpaths':
				break;
			default:
				$localeNode = $node->appendChild( $node->ownerDocument->createElement( 'locale' ) );
				$localeNode->setAttribute( 'name', $loc );
				foreach( $data as $module => $data2 ) {
					$moduleNode = $localeNode->appendChild( $localeNode->ownerDocument->createElement( 'module' ) );
					$moduleNode->setAttribute( 'name', $module );
					foreach( $data2 as $k => $v ) {
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
	 * @param \DOMNode $node
	 * @param array    $arr
	 * @param array    $allxpaths
	 * @param string   $xpath
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
					'text'   => $item->nodeValue,
					'usage'  => ( $usage = $this->findUsages( $xpath ) )
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
			$types     = $resourcer->findInstances();
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
			$types     = $resourcer->findInstances();
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
			$dirs        = \Difra\Action::getInstance()->getControllerPaths();
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
}