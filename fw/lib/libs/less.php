<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

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