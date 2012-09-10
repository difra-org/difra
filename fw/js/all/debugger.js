var debug = {};

debug.toggle = function() {
	$( '#debug' ).toggleClass( 'max' );
};

debug.switch = function() {
	$( '#debug li.selected' ).removeClass( 'selected' );
	$( this ).parent( 'li' ).addClass( 'selected' );
};

$( document ).on( 'click dblclick', '#debug .tab-title', debug.switch );