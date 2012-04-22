$( document ).keydown( function ( e ) {

	if( e.ctrlKey ) {
		switch( e.keyCode ) {
			case 0x27:
				var h = $( '.pagerNext > a' ).attr( 'href' );
				if( typeof h !== 'undefined' ) {
					window.location = h;
				}
				break;
			case 0x25:
				var h = $( '.pagerPrev > a' ).attr( 'href' );
				if( typeof h !== 'undefined' ) {
					window.location = h;
				}
				break;
		}
	}

} );