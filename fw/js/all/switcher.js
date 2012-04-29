/**
 * Переключает страницы с помощью ajax.
 * В исходной и конечной странице должны присутствовать контейнеры #content,
 * которые данный скрипт заменяет при переключении.
 *
 * Добавляет события:
 * construct	— срабатывает после смены страницы
 * destruct	— срабатывает перед сменой страницы (в том числе при перенаправлении на новый адрес в случае неудачи)
 * switch	— срабатывает перед удачной ajax-сменой страницы
 */
var switcher = [];

switcher.hashChanged = false;
switcher.noPush = false;
switcher.url = false;
switcher.ajaxConfig = {
	async: true,
	cache: false,
	headers: { 'X-Requested-With' : 'SwitchPage' },
	type: 'GET',
	beforeSend: function() {
		$( '#loading' ).css( 'display', 'block' );
	},
	success: function( data, status, xhr ) {
		$( document ).triggerHandler( 'destruct' );
		var newdata = $( data );
		var a = newdata.filter( '#content,.switcher' );
		if( !a.length ) {
			$( '#loading' ).css( 'display', 'none' );
			document.location = switcher.url;
		}
		if( !switcher.noPush ) {
			if( history.pushState ) {
				history.pushState( { url: switcher.url }, null, switcher.url );
			} else { // workaround для убогих (IE, Opera, Android)
				switcher.hashChanged = true;
				window.location = '/#!' + switcher.url;
			}
			if( typeof _gaq == 'object' && _gaq.push ) {
				_gaq.push( ['_trackPageview', switcher.url] );
			}
		}
		$( document ).triggerHandler( 'switch' );

		a.each( function( k, v ) {
			try {
				$( '#' + $( v ).attr( 'id' ) ).replaceWith( v ).remove();
			} catch( e ) {}
		} );
		$( window ).scrollTop( 0 );

		var title = newdata.filter( 'title' ).text();
		if( title.length ) {
			document.title = title;
		}
		$( '#loading' ).css( 'display', 'none' );
		$( document ).triggerHandler( 'construct' );
	},
	error: function( xhr ) {
		$( document ).triggerHandler( 'destruct' );
		$( '#loading' ).css( 'display', 'none' );
		document.location = switcher.url;
	}
};

switcher.page = function( url, noPush, data ) {

	switcher.noPush = noPush ? true : false;
	switcher.url = url;
	// filter protocol://host part
	var host = window.location.protocol + "//" + window.location.host + "/";
	if( host == switcher.url.substring( 0, host.length ) ) {
		switcher.url = switcher.url.substring( host.length - 1 );
	}
	if( !$( '#content,.switcher' ).length ) {
		$( document ).triggerHandler( 'destruct' );
		$( '#loading' ).css( 'display', 'none' );
		document.location = switcher.url;
	}
	if( data ) {
		var conf = switcher.ajaxConfig;
		conf.type = 'POST';
		conf.data = data;
		$.ajax( url, conf );
	} else {
		$.ajax( url, switcher.ajaxConfig );
	}
};

window.onhashchange = function() {

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

window.onpopstate = function() {

	if( switcher.url && switcher.url != document.location.pathname && switcher.url != document.location.hash.substring( 2 ) ) {
		switcher.page( document.location.href, true );
	}
};

$( document ).ready( function() {

	if( document.location.hash && document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
		if( history.replaceState ) {
			switcher.hashChanged = true;
			history.replaceState( { url: switcher.url }, null, switcher.url );
		}
	} else if( !history.pushState && document.location.hash.substring( 0, 2 ) != '#!' ) {
//		switcher.page( document.location.href ); // это приведёт к переходу на hash-ссылку при открытии обычной ссылки
	} else {
		if( !switcher.url ) {
			switcher.url = document.location.pathname;
		}
	}
} );

$( 'a' ).live( 'click dblclick',
	function( event ) {
		if( $( this ).hasClass( 'ajaxer' ) || $( this ).hasClass( 'noAjaxer' ) ) {
			return;
		}
		if( $( this ).attr( 'href' ) && $( this ).attr( 'href' ).substring( 0, 11 ) == 'javascript:' ) {
		} else if( $( this ).attr( 'href' ) == '#' ) {
			event.preventDefault();
		} else if( $( this ).attr( 'href' ) && $( this ).attr( 'href' ).substring( 0, 1 ) == '#' ) {
		} else if( $( this ).attr( 'href' ) ) {
			event.preventDefault();
			switcher.page( $( this ).attr( 'href' ) );
		}
	} );
