/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

/**
 * Переключает страницы с помощью ajax.
 * В исходной и конечной странице должны присутствовать контейнеры .switcher,
 * которые данный скрипт заменяет при переключении.
 *
 * Добавляет события:
 * construct        — срабатывает после смены страницы
 * destruct        — срабатывает перед сменой страницы (в том числе при перенаправлении на новый адрес в случае неудачи)
 * switch        — срабатывает перед удачной ajax-сменой страницы
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
		/*
		 var newPath = '/';
		 var content = newdata.find( '#content' );
		 if( content.length && content.attr( 'basepath' ) ) {
		 newPath = content.attr( 'basepath' );
		 }
		 if( switcher.basePath != newPath ) {
		 switcher.fallback();
		 return;
		 }
		 */
		$( document ).triggerHandler( 'destruct' );
		if( !switcher.noPush ) {
			if( typeof history.pushState == 'function' ) {
				history.pushState( { url: switcher.url }, null, switcher.url );
			} else { // нет pushState — используем хеши
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

switcher.fallback = function () {

	$( document ).triggerHandler( 'destruct' );
	loading.hide();
	document.location = switcher.url;
};

switcher.page = function ( url, noPush, data ) {

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

window.onpopstate = function () {

	if( switcher.url && switcher.url != decodeURI( document.location.pathname ) && switcher.url != document.location.hash.substring( 2 ) ) {
		switcher.page( document.location.href, true );
	}
};

$( document ).ready( function () {

	/*
	 var content = $( '#content' );
	 if( content.length && content.attr( 'basepath' ) ) {
	 switcher.basePath = content.attr( 'basepath' );
	 } else {
	 switcher.basePath = '/';
	 }
	 */
	if( document.location.hash && document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
		if( typeof history.replaceState == 'function' ) {
			switcher.hashChanged = true;
			history.replaceState( { url: switcher.url }, null, switcher.url );
		}
	} else if( typeof history.pushState != 'function' && document.location.hash.substring( 0, 2 ) != '#!' && content.length ) {
		switcher.page( document.location.href ); // это приведёт к переходу на hash-ссылку при открытии обычной ссылки
	} else {
		if( !switcher.url ) {
			switcher.url = decodeURI( document.location.pathname );
		}
	}
} );

$( document ).on( 'click dblclick', 'a', function ( event ) {
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
