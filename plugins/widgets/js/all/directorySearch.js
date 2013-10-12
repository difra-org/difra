function directorySearch( elem ) {
	var val = $( elem ).val().toLowerCase();
	$( '#DirectoryWindow .searchable tr' ).each( function() {
		if( $( this ).find( '.search-me' ).text().toLowerCase().indexOf( val ) == -1 ) {
			$( this ).slideUp();
		} else {
			$( this ).slideDown();
		}
	} );
}

$( document ).on( 'click dblclick', '.widgets-directory', function() {
	$( '.widgets-directory.last' ).removeClass( 'last' );
	$( this ).addClass( 'last' );
} );