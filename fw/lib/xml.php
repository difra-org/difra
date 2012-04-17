<?php

namespace Difra;

class XML {

	/**
	 * @static
	 * @return XML
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * @param \DOMNode $node
	 * @param array    $array
	 */
	public function array2xml( $node, &$array ) {

		if( is_array( $array ) and !empty( $array ) ) {
			foreach( $array as $k => $v ) {
				if( !is_array( $v ) ) {
					$node->appendChild( $node->ownerDocument->createElement( $k, $v ) );
				} else {
					$subNode = $node->appendChild( $node->ownerDocument->createElement( $k ) );
					$this->array2xml( $subNode, $v );
				}
			}
		}
	}
}