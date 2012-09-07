<?php

namespace Difra\Libs\XML;

class DOM {

	/* Временно для array2xml */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Функция для переименования ноды в DOM-документе
	 *
	 * @static
	 *
	 * @param \DOMNode $node
	 * @param string   $newName
	 */
	static public function renameNode( $node, $newName ) {

		$newNode = $node->ownerDocument->createElement( $newName );
		if( $node->hasAttributes() ) {
			foreach( $node->attributes as $attribute ) {
				$newNode->setAttribute( $attribute->nodeName, $attribute->nodeValue );
			}
		}
		while( $node->firstChild ) {
			$newNode->appendChild( $node->firstChild );
		}
		$node->parentNode->replaceChild( $newNode, $node );
	}

	/**
	 * @param \DOMNode $node
	 * @param array    $array
	 */
	public static function array2dom( &$node, &$array ) {

		if( is_array( $array ) and !empty( $array ) ) {
			foreach( $array as $k => $v ) {
				if( !is_array( $v ) ) {
					$node->appendChild( $node->ownerDocument->createElement( $k, $v ) );
				} else {
					$subNode = $node->appendChild( $node->ownerDocument->createElement( $k ) );
					self::array2dom( $subNode, $v );
				}
			}
		}
	}

	public function array2xml( &$node, &$array ) {

		trigger_error( 'Function \Difra\Libs\XML\DOM->array2xml() is deprecated. Please use function \Difra\Libs\XML\DOM::array2dom().' );
		self::array2dom( $node, $array );
	}
}