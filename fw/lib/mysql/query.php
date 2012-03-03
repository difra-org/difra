<?php

namespace Difra\MySQL;

abstract class Query {

	static public function factory( $fragments ) {

		$query = null;
		switch( strtoupper( $fragments[0] ) ) {
		case 'CREATE':
			return new Query\Create( $fragments );
		case 'DROP':
		case 'INSERT':
		case 'SET':
			//echo 'Warning! Ignored ' . $fragments[0] . '<br/>';
			break;
		default:
			throw new \Difra\Exception( 'Unknown MySQL keyword: ' . $fragments[0] );
		}
		return $query;
	}

	public function toString( $str = null ) {

		if( is_null( $str ) ) {
			$str = $this->fragments;
		}
		foreach( $str as $k=>$ent ) {
			if( is_array( $ent ) ) {
				$str[$k] = '(' . $this->toString( $ent ) . ')';
			}
		}
		return implode( ' ', $str );
	}

	public function __toString() {

		return $this->toString();
	}

	protected function quote( $str ) {

		if( mb_substr( $str, 0, 1 ) != '`' or mb_substr( $str, -1 ) != '`'  ) {
			return "`$str`";
		}
		return $str;
	}

	protected function unquote( $str ) {

		if( mb_substr( $str, 0, 1 ) == '`' and mb_substr( $str, -1 ) == '`' ) {
			return mb_substr( $str, 1, mb_strlen( $str ) - 2 );
		}
		return $str;
	}

}