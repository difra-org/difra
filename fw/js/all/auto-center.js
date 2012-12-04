function autocenter() {
	$( '.auto-center' ).each( function( index, elem ) {
		$( elem ).css( {
				       left: ( $( window ).width() - $( elem ).outerWidth( false ) ) / 2,
				       top: ( $( window ).height() - $( elem ).outerHeight( false ) ) / 2
			       } );
	} );
}

$( window ).resize( autocenter );