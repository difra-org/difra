/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

$( document ).on( 'construct', function () {

	// Google Analytics

	//noinspection JSUnresolvedVariable
	if( typeof _gaq == 'object' && typeof _gaq.push == 'function' ) {
		//noinspection JSUnresolvedVariable
		_gaq.push( ['_trackPageview', switcher.url] );
	}

	// Yandex Metrika

	for( var y in window ) {
		if( typeof( window[y] ) != 'object' || !y.match( /^yaCounter[0-9]*$/ ) ) {
			continue;
		}
		window[y].hit( switcher.url, document.title, document.referrer );
	}
} );