<?php

namespace Difra;

class Menu {

	public $menu = null;

	static public function getInstance( $instance ) {

		static $_instances = array();
		return isset( $_instances[$instance] ) ? $_instances[$instance] : $_instances[$instance] = new self( $instance );
	}

	public function __construct( $instance ) {

		$this->menu = new \DOMDocument();
		$this->menu->loadXML( Resourcer::getInstance( 'menu' )->compile( $instance ) );
	}
	
	public function getXML( $node ) {
		
		$node->appendChild( $node->ownerDocument->importNode( $this->menu->documentElement, true ) );
		return true;
	}

	/*
	public function getPaths() {

		static $_paths = null;
		if( is_null( $_paths ) ) {
			$_paths = array();
			if( $this->menu ) {
				$_paths = $this->_getSubPaths( $this->menu->documentElement );
			}

		}
		return $_paths;
	}

	private function _getSubPaths( $node ) {

		$newPaths = array();
		if( $node->hasAttributes() ) {
			$data = array();
			foreach( $node->attributes as $v ) {
				$data[$v->nodeName] = $v->nodeValue;
			}
			$newPaths[] = $data;
		}

		foreach( $node->childNodes as $item ) {
			if( $item->nodeType == XML_ELEMENT_NODE ) {
				$newPaths2 = $this->_getSubPaths( $item );
				$newPaths = array_merge( $newPaths, $newPaths2 );
			}
		}
		return $newPaths;
	}

	public function getCurrent( $search ) {

		static $_current = null;
		if( is_null( $_current ) or 1 ) {
			$paths = $this->getPaths();
			if( !empty( $paths ) ) {
				$searchA = explode( '/', trim( $search, '/' ) );
				$foundPath = '';
				$foundA = array();
				foreach( $paths as $data ) {
					if( !isset( $data['href'] ) ) {
						continue;
					}
					$pathA = explode( '/', trim( $data['href'], '/' ) );
					if( sizeof( $pathA ) <= sizeof( $searchA ) ) {
						$tmpA = array();
						for( $i = 0; $i < sizeof( $pathA ); $i++ ) {
							if( $pathA[$i] == $searchA[$i] ) {
								$tmpA[] = $searchA[$i];
								if( $i >= sizeof( $foundA ) ) {
									$foundA = $tmpA;
									$foundPath = $data['href'];
								}
							} else {
								continue;
							}
						}
					}
				}
				if( $foundPath ) {
					$_current = $foundPath;
				} else {
					$_current = false;
				}
			} else {
				$_current = false;
			}
		}
		return $_current;
	}
	 */
}

