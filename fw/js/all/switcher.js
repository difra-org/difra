/**
 * Переключает страницы с помощью ajax.
 * В исходной и конечной странице должны присутствовать контейнеры #content,
 * которые данный скрипт заменяет при переключении.
 *
 * Добавляет события:
 * construct        — срабатывает после смены страницы
 * destruct        — срабатывает перед сменой страницы (в том числе при перенаправлении на новый адрес в случае неудачи)
 * switch        — срабатывает перед удачной ajax-сменой страницы
 */
var switcher = [];

switcher.hashChanged = false;
switcher.noPush = false;
switcher.url = false;
switcher.basePath = '/';
switcher.ajaxConfig = {
	async: true,
	cache: false,
	headers: { 'X-Requested-With': 'SwitchPage' },
	type: 'GET',
	beforeSend: function() {
		$( '#loading' ).css( 'display', 'block' );
	},
	success: function( data, status, xhr ) {
		try {
			var newdata = $( data );
		} catch( e ) {
			switcher.fallback();
			return;
		}
		var a = newdata.filter( '#content,.switcher' ).add( newdata.find( '#content,.switcher' ) );
		if( !a.length ) {
			switcher.fallback();
			return;
		}
		var newPath = '/';
		var content = newdata.filter( '#content' );
		if( !content.length ) {
			content = newdata.find( '#content' );
		}
		if( content.length && content.attr( 'basepath' ) ) {
			newPath = content.attr( 'basepath' );
		}
		if( switcher.basePath != newPath ) {
			switcher.fallback();
			return;
		}
		$( document ).triggerHandler( 'destruct' );
		if( !switcher.noPush ) {
			if( typeof history.pushState == 'function' ) {
				history.pushState( { url: switcher.url }, null, switcher.url );
			} else { // нет pushState — используем хеши
				switcher.hashChanged = true;
				window.location = switcher.basePath + '#!' + switcher.url;
			}
			if( typeof _gaq == 'object' && typeof _gaq.push == 'function' ) {
				_gaq.push( ['_trackPageview', switcher.url] );
			}
		}
		$( document ).triggerHandler( 'switch' );

		a.each( function( k, v ) {
			try {
				$( '#' + $( v ).attr( 'id' ) ).replaceWith( v ).remove();
			} catch( e ) {
			}
		} );
		$( window ).scrollTop( 0 );

		var title = newdata.filter( 'title' ).text();
		if( title.length ) {
			document.title = title;
		}
		$( document ).triggerHandler( 'construct' );
		$( '#loading' ).css( 'display', 'none' );
	},
	error: function( xhr ) {
		switcher.fallback();
	}
};

switcher.fallback = function() {

	$( document ).triggerHandler( 'destruct' );
	$( '#loading' ).css( 'display', 'none' );
	document.location = switcher.url;
};

switcher.page = function( url, noPush, data ) {

	// filter protocol://host part
	var host = window.location.protocol + "//" + window.location.host + "/";
	if( host == url.substring( 0, host.length ) ) {
		switcher.page( url.substring( host.length - 1 ) );
		return;
	}
	if( typeof debug != 'undefined' ) {
		debug.addReq( 'Switching page: ' + url );
	}
	switcher.noPush = noPush ? true : false;
	switcher.url = url;
	if( typeof data != 'undefined' ) {
		var conf = switcher.ajaxConfig;
		conf.type = 'POST';
		conf.data = data;
		$.ajax( url, conf );
	} else if( !$( '#content,.switcher' ).length ) {
		$( document ).triggerHandler( 'destruct' );
		$( '#loading' ).css( 'display', 'none' );
		window.location = switcher.url;
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

	var content = $( '#content' );
	if( content.length && content.attr( 'basepath' ) ) {
		switcher.basePath = content.attr( 'basepath' );
	} else {
		switcher.basePath = '/';
	}
	if( document.location.hash && document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
		if( typeof history.replaceState == 'function' ) {
			switcher.hashChanged = true;
			history.replaceState( { url: switcher.url }, null, switcher.url );
		}
	} else if( typeof history.pushState != 'function' && document.location.hash.substring( 0, 2 ) != '#!' ) {
		switcher.page( document.location.href ); // это приведёт к переходу на hash-ссылку при открытии обычной ссылки
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
		       var href = $( this ).attr( 'href' );
		       if( href == '#' ) {
			       event.preventDefault();
		       } else if( href && href.substring( 0, 11 ) != 'javascript:' && href.substr( 0, 1 ) != '#' ) {
			       event.preventDefault();
			       switcher.page( $( this ).attr( 'href' ) );
		       }
	       } );
