<?php

namespace Difra\Libs;

use Difra\Debugger;

include_once( __DIR__ . '/less/lessc.inc.php' );

class Less {

	public static function compile( $string ) {

		static $less = null;
		if( !$less ) {
			$less = new \lessc;
			if( !Debugger::getInstance()->isEnabled() ) {
				$less->setFormatter( 'compressed' );
				$less->setPreserveComments( false );
			} else {
				$less->setFormatter( 'lessjs' ); // or 'lessjs' ?
				$less->setPreserveComments( true );
			}
		}

		return $less->compile( $string );
	}
}