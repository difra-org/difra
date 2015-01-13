/**
 * Script for switching pages with Ctrl+Left and Ctrl+Right.
 * TODO: fix it to work with Alt+arrow under Mac OS X.
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