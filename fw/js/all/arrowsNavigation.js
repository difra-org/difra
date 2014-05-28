/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

$( document ).on( 'keydown', function ( e ) {

	if( e.ctrlKey ) {
		//noinspection SwitchStatementWithNoDefaultBranchJS
		switch( e.keyCode ) {
		case 0x27: // right arrow
			var h = $( '.pagerNext > a' ).attr( 'href' );
			if( typeof h !== 'undefined' ) {
				window.location = h;
			}
			break;
		case 0x25: // left arrow
			h = $( '.pagerPrev > a' ).attr( 'href' );
			if( typeof h !== 'undefined' ) {
				window.location = h;
			}
			break;
		}
	}

} );