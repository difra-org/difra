var debug = {};

debug.toggle = function() {
	$( '#debug' ).toggleClass( 'max' );
};

debug.switch = function() {
	$( '#debug li.selected' ).removeClass( 'selected' );
	$( this ).parent( 'li' ).addClass( 'selected' );
};

debug.reqData = '';
debug.addReq = function( message ) {
	if( message ) {
		debug.reqData += '<tr><td>' + ( $( '<div/>' ).text( message ).html() ) + '</td></tr>';
	}
	$( '#debug-requests' ).html( debug.reqData );
};

$( document ).on( 'click dblclick', '#debug .tab-title', debug.switch );
$( document ).ready( function() {
	debug.addReq( 'Fresh page loaded: ' + document.location.pathname );
} );
$( document ).bind( 'construct', function() {
	debug.addReq( 'Ajax page loaded: ' + document.location.pathname );
} );
