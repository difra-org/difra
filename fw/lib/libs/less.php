<?php

namespace Difra\Libs;

include_once( DIR_FW . 'lib/libs/less/lessc.inc.php' );

class Less {

	public static function compile( $string ) {

		static $less = null;
		if( !$less ) {
			$less = new \lessc;
			if( !\Difra\Debugger::isEnabled() ) {
				$less->setFormatter( 'compressed' );
				$less->setPreserveComments( false );
			} else {
				$less->setFormatter( 'lessjs' );
				$less->setPreserveComments( true );
			}
		}

		return $less->compile( $string );
	}
}