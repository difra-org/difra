<?php

namespace Difra\Libs\XML;

class DOM {

	/**
	 * Временно для array2xml
	 *
	 * @deprecated
	 * @static
	 * @return DOM
	 */
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
	 * Создаёт XML, в котором значения элементов массива становятся значениями нод
	 *
	 * @static
	 *
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

	/**
	 * Создаёт XML, в котором значения элементов массива становятся значениями аттрибутов
	 *
	 * @static
	 *
	 * @param \DOMElement $node
	 * @param array       $array
	 * @param bool        $verbal
	 */
	public static function array2domAttr( &$node, &$array, $verbal = false ) {

		if( is_array( $array ) and !empty( $array ) ) {
			foreach( $array as $k => $v ) {
				if( is_numeric( $k ) and !is_array( $v ) ) {
					$node->appendChild( $node->ownerDocument->createElement( $v ) );
				} elseif( is_array( $v ) ) {
					if( is_numeric( $k ) ) {
						$k = "_$k";
					}
					$newNode = $node->appendChild( $node->ownerDocument->createElement( $k ) );
					self::array2domAttr( $newNode, $v, $verbal );
				} elseif( is_object( $v ) ) {
				} else {
					if( $verbal ) {
						if( is_null( $v ) ) {
							$v = 'null';
						} elseif( $v === false ) {
							$v = 'false';
						} elseif( $v === true ) {
							$v = 'true';
						} elseif( $v === 0 ) {
							$v = '0';
						}
					}
					if( is_numeric( $k ) ) {
						$k = "_$k";
					}
					$node->setAttribute( $k, $v );
				}
			}
		}
	}

	/**
	 * @deprecated
	 *
	 * @param $node
	 * @param $array
	 */
	public function array2xml( &$node, &$array ) {

		trigger_error( 'Function \Difra\Libs\XML\DOM->array2xml() is deprecated. Please use function \Difra\Libs\XML\DOM::array2dom().' );
		self::array2dom( $node, $array );
	}
}