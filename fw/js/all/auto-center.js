function autocenter() {
	$( this ).css( {
		left: ( $( window ).width() - $( this ).outerWidth() ) / 2,
		top: ( $( window ).height() - $( this ).outerHeight() ) / 2
	} );
}

$( window ).resize( autocenter );