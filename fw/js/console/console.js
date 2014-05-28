/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

var debug = {};

debug.toggle = function () {
	var deb = $( '#debug' );
	deb.toggleClass( 'max' );
	if( deb.hasClass( 'max' ) ) {
		$.cookie( 'debugMax', '1' );
	} else {
		$.cookie( 'debugMax', '0' );
	}
};

debug.switchTab = function () {
	$( '#debug li.selected' ).removeClass( 'selected' );
	$( this ).parent( 'li' ).addClass( 'selected' );
};

debug.toggleDebugger = function () {

	var deb = $.cookie( 'debug' ) != '0';
	$.cookie( 'debug', deb ? '0' : '1' );
	ajaxer.reload();
};

debug.toggleConsole = function () {

	var deb = $.cookie( 'debugConsole' ) != '0';
	$.cookie( 'debugConsole', deb ? '0' : '1' );
	ajaxer.reload();
};

debug.toggleCache = function () {

	var caches = $.cookie( 'cachesEnabled' ) != '1';
	$.cookie( 'cachesEnabled', caches ? '1' : '0' );
	ajaxer.reload();
};

debug.reqData = '';
debug.addReq = function ( message ) {
	if( message ) {
		debug.reqData += '<tr><td>' + ( $( '<div></div>' ).text( message ).html() ) + '</td></tr>';
	}
	$( '#debug-requests' ).html( debug.reqData );
};

$( document ).on( 'click dblclick', '#debug .tab-title', debug.switchTab );
$( document ).ready( function () {
	debug.addReq( 'Fresh page loaded: ' + document.location.pathname );
	if( $.cookie( 'debugMax' ) == '1' ) {
		$( '#debug' ).addClass( 'max' );
	}
} );
$( document ).bind( 'construct', function () {
	debug.addReq( 'Ajax page loaded: ' + document.location.pathname );
} );
