/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

$( document ).on( 'click dblclick', '#menu_adm > ul > li', function () {
	$( this ).addClass( 'clicked' );
	$( '#menu_adm > ul > li' ).each( function () {
		if( !$( this ).hasClass( 'clicked' ) ) {
			$( this ).children( 'ul' ).slideUp( 'fast' );
		}
	} );
	$( this ).removeClass( 'clicked' );
	var child = $( this ).children( 'ul' );
	if( child.css( 'display' ) == 'none' ) {
		child.slideDown( 'fast' );
	} else {
		child.slideUp( 'fast' );
	}
} );

$( document ).on( 'click dblclick', '#menu_adm > ul > li > ul', function () {
	return false;
} );