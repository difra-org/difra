/**
 * Switcher.js allows native-like page switching with ajax.
 *
 * Server identifies Switcher.js requests by "X-Requested-With: SwitchPage" HTTP header.
 * When this header is detected, you can return only updated elements. Usually there is no point
 * to track which elements was updated, but you can return very simplified page with container elements only,
 * with all layout missing. To identify which elements to replace, this script uses id attribute. All container
 * elements you want to update on page switch should contain .switcher class.
 *
 * If script fails to switch link, it uses http redirect to change page.
 *
 * This script adds following events:
 * construct        fires after page change
 * destruct        fires before page change
 * switch        fires after destruct when script understands that page switch will be successful
 */

var switcher = [];

switcher.basePath = '/';
switcher.hashChanged = false;
switcher.noPush = false;
switcher.url = false;
switcher.referrer = false;

switcher.ajaxConfig = {
	async: true,
	cache: false,
	headers: { 'X-Requested-With': 'SwitchPage' },
	type: 'GET',
	beforeSend: function () {
		loading.show();
	},
	success: function ( data, status, xhr ) {
		try {
			var newdata = $( data );
		} catch( ignore ) {
			switcher.fallback();
			return;
		}
		var a = newdata.filter( '.switcher' ).add( newdata.find( '.switcher' ) );
		if( !a.length ) {
			switcher.fallback();
			return;
		}
		$( document ).triggerHandler( 'destruct' );
		if( !switcher.noPush ) {
			if( typeof history.pushState == 'function' ) {
				history.pushState( { url: switcher.url }, null, switcher.url );
			} else { // browser does not support pushState, use hashes
				switcher.hashChanged = true;
				window.location = switcher.basePath + '#!' + switcher.url;
			}
		}
		$( document ).triggerHandler( 'switch' );

		a.each( function ( k, v ) {
			try {
				$( '#' + $( v ).attr( 'id' ) ).replaceWith( v ).remove();
			} catch( ignore ) {
			}
		} );
		$( window ).scrollTop( 0 );

		var title = newdata.filter( 'title' ).text();
		if( title.length ) {
			document.title = title;
		} else {
			title = newdata.find( 'title' ).text();
			if( title.length ) {
				document.title = title;
			}
		}
		$( document ).triggerHandler( 'construct' );
		loading.hide();
	},
	error: function ( xhr ) {
		switcher.fallback();
	}
};

/**
 * Page switch fall back
 */
switcher.fallback = function () {

	$( document ).triggerHandler( 'destruct' );
	loading.hide();
	document.location = switcher.url;
};

/**
 * Switch page
 * @param url
 * @param noPush
 * @param data
 */
switcher.page = function ( url, noPush, data ) {

	// cut protocol://host part if it matches current host
	var host = window.location.protocol + "//" + window.location.host + "/";
	if( host == url.substring( 0, host.length ) ) {
		switcher.page( url.substring( host.length - 1 ) );
		return;
	}
	if( typeof debug != 'undefined' ) {
		debug.addReq( 'Switching page: ' + url );
	}
	switcher.noPush = noPush ? true : false;
	switcher.referrer = switcher.url;
	switcher.url = url;
	if( typeof data == 'undefined' ) {
		if( $( '.switcher' ).length ) {
			$.ajax( url, switcher.ajaxConfig );
		} else {
			$( document ).triggerHandler( 'destruct' );
			loading.hide();
			window.location = switcher.url;
		}
	} else {
		var conf = switcher.ajaxConfig;
		conf.type = 'POST';
		conf.data = data;
		$.ajax( url, conf );
	}
};

/**
 * Support "Back" and "Forward" browser buttons for browsers without pushState
 */
window.onhashchange = function () {

	if( switcher.hashChanged ) {
		switcher.hashChanged = false;
		return;
	}
	if( document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
	} else {
		switcher.page( document.location.href, true );
	}
};

/**
 * Support "Back" and "Forward" browser buttons
 */
window.onpopstate = function () {

	if( switcher.url && switcher.url != decodeURI( document.location.pathname ) && switcher.url != document.location.hash.substring( 2 ) ) {
		switcher.page( document.location.href, true );
	}
};

/**
 * Init
 */
$( document ).ready( function () {

	if( document.location.hash && document.location.hash.substring( 0, 2 ) == '#!' ) {
		// redirect /#!/some/page to /some/page in smart browsers
		switcher.page( document.location.hash.substring( 2 ), true );
		if( typeof history.replaceState == 'function' ) {
			switcher.hashChanged = true;
			history.replaceState( { url: switcher.url }, null, switcher.url );
		}
	} else if( typeof history.pushState != 'function' && document.location.hash.substring( 0, 2 ) != '#!' && content.length ) {
		// redirect /some/page to /#!/some/page in stupid browsers
		switcher.page(document.location.href);
	} else {
		if( !switcher.url ) {
			// remember current URL on first page load
			switcher.url = decodeURI( document.location.pathname );
		}
	}
} );

$( document ).on( 'click dblclick', 'a', function ( event ) {
	if( $( this ).hasClass( 'ajaxer' ) || $( this ).hasClass( 'noAjaxer' ) ) {
		// skip link if it has class .ajaxer or .noAjaxer
		return;
	}

	var href = $( this ).attr( 'href' );
	if( href == '#' ) {
		// do nothing on href="#" links
		event.preventDefault();
	} else if( href && href.substring( 0, 11 ) != 'javascript:' && href.substr( 0, 1 ) != '#' ) {
		// link is not javascript and not anchor, use ajax page switching
		event.preventDefault();
		switcher.page( $( this ).attr( 'href' ) );
	}
} );
