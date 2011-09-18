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
switcher.first = true;
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
		var a = $( data ).children( '#content' );
		if( !a.length ) {
			$( '#loading' ).css( 'display', 'none' );
			document.location = switcher.url;
		}
		if( !switcher.noPush ) {
			if( history.pushState ) {
				history.pushState( { url: switcher.url }, null, switcher.url );
			} else {
				switcher.hashChanged = true;
				window.location = '/#!' + switcher.url;
			}
		}
		$( document ).triggerHandler( 'switch' );
		$( '#content' ).replaceWith( a ).remove();
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
	if( !$( '#content' ).length ) {
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

window.onpopstate = function() {
	if( switcher.first ) {
		switcher.first = false;
		return;
	}
	switcher.page( document.location.href, true );
};

window.onhashchange = function() {
	if( !switcher.hashChanged && document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
	} else {
		switcher.hashChanged = false;
	}
};

$( document ).ready( function() {
	if( document.location.hash && document.location.hash.substring( 0, 2 ) == '#!' ) {
		switcher.page( document.location.hash.substring( 2 ), true );
	}
} );

function switchPage( url, noPush, data ) {

	switcher.page( url, noPush, data );
}

$( 'a' ).live( 'click dblclick',
	function( event ) {
		if( $( this ).attr( 'href' ) && $( this ).attr( 'href' ).substring( 0, 1 ) != '#' ) {
			event.preventDefault();
			switcher.page( $( this ).attr( 'href' ) );
		} else if( $( this ).attr( 'href' ) == '#' ) {
			event.preventDefault();
		}
	} );

$( 'form' ).live( 'submit', function( event ) {
	if( $( this ).hasClass( 'ajaxer' ) ) {
		return;
	}
	event.preventDefault();
	if( !$( event.target ).attr( 'action' ) ) {
		event.target = $( event.target ).closest( 'form' );

	}
	switcher.page( $( event.target ).attr( 'action' ), true, $( event.target ).serialize() );
} );
