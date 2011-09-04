function autocenter() {
	$( '.auto-center' ).each( function( index, elem ) {
		$( elem ).css( {
			left: ( $( window ).width() - $( elem ).outerWidth() ) / 2,
			top: ( $( window ).height() - $( elem ).outerHeight() ) / 2
		} );
	} );
}

$( window ).resize( autocenter );