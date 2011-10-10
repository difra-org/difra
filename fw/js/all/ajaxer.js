/**
 * Отправляет ajax-запросы и обрабатывает результаты.
 *
 * Добавляет события:
 * form-submit		— срабатывает перед отправкой данных формы
 */
$.ajaxSetup( {
	async : false,
	cache :	false,
	headers : {
		'X-Requested-With' : 'XMLHttpRequest'
	}
} );

var ajaxer = {};
ajaxer.id = 1;

ajaxer.httpRequest = function( url, params, headers ) {

	var data = {};
	if( typeof params == 'undefined' ) {
		data.type = 'GET';
	} else {
		data.type = 'POST';
		data.data = 'json=' + encodeURIComponent( JSON.stringify( params ) );
	}
	if( typeof headers != 'undefined' ) {
		data.headers = headers;
	}
	return $.ajax( url, data ).responseText;
};

ajaxer.sendForm = function( form, event ) {

	var jForm = $( form );
	var data = {
		form: jForm.serializeArray()
	};
	//var data = $( event.target ).serialize();
	$( form ).find( '.required' ).fadeOut( 'fast' );
	$( form ).find( '.invalid' ).fadeOut( 'fast' );
	$( form ).find( '.status' ).fadeOut( 'fast' );
	ajaxer.process( this.httpRequest( jForm.attr( 'action' ), data ), form );
};

ajaxer.query = function( url, data ) {
	
	ajaxer.process( this.httpRequest( url, data ) );
};

ajaxer.process = function( data, form ) {

	try {
		console.info( 'Server said: ' + data );
		var data1 = $.parseJSON( data );
		if( !data1.actions ) {
			throw "data error";
		}
		for( var key in data1.actions ) {
			var action = data1.actions[key];
			switch( action.action ) {
			case 'notify':	// сообщение
				this.notify( action.lang, action.message );
				break;
			case 'require':// не заполнено обязательное поле формы
				this.require( form, action.name );
				break;
			case 'invalid':	// не правильное значение поля формы
				this.invalid( form, action.name, action.message );
				break;
			case 'status': // текстовый статус для поля
				this.status( form, action.name, action.message, action.classname );
				break;
			case 'redirect':// перенаправление
				this.redirect( action.url );
				break;
			case 'display':	// показать окно с пришедшим html
				this.display( action.html );
				break;
			case 'reload': // перезагрузить страницу
				this.reload();
				break;
			case 'close':
				this.close( form );
				break;
			case 'error':	// сообщение об ошибке
				this.error( action.lang, action.message );
				break;
			default:
				console.warn( 'Ajaxer action "' + action.action + '" not implemented' );
			}
		}
	} catch( ex ) {
		this.notify( {close:'OK'}, 'Unknown error.' );
		console.warn( 'Server returned:', data );
	}
};

ajaxer.notify = function( lang, message ) {

	$( 'body' ).append(
		'<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
			'<div class="overlay-container auto-center">' +
			'<div class="overlay-inner" style="display:none">' +
			'<div class="close-button" onclick="ajaxer.close(this)"></div>' +
			'<p>' + message + '</p>' +
			'<a href="#" onclick="ajaxer.close(this)" class="popup-button center">' + lang.close + '</a>' +
			'</div>' +
			'</div>' +
			'</div>'
	);
	$( '#ajaxer-' + ajaxer.id ).find( '.overlay-inner' ).fadeIn( 'fast' );
	$( window ).resize();
	ajaxer.id++;
};

ajaxer.error = function( lang, message ) {

	ajaxer.notify( lang, message );
};

ajaxer.require = function( form, name ) {

	var req = $( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.required' );
	if( req ) {
		req.fadeIn();
	}
};

ajaxer.invalid = function( form, name, message ) {

	var inv = $( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.invalid' );
	if( inv ) {
		inv.fadeIn( 'fast' );
		inv.find( '.invalid-text' ).html( message );
	}
};

ajaxer.status = function( form, name, message, classname ) {

	var status = $( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.status' );
	if( status ) {
		status.fadeIn( 'fast' );
		status.attr( 'class', 'status ' + classname );
		status.html( message );
	}
};

ajaxer.redirect = function( url ) {

	if( typeof(switcher) != 'undefined' ) {
		switcher.page( url );
	} else {
		document.location( url );
	}
};

ajaxer.reload = function() {

	window.location.reload();
};

ajaxer.display = function( html ) {

	$( 'body' ).append(
		'<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
			'<div class="overlay-container auto-center">' +
			'<div class="overlay-inner" style="display:none">' +
			'<div class="close-button" onclick="ajaxer.close(this)"></div>' +
			html +
			'</div>' +
			'</div>' +
			'</div>'
	);
	$( '#ajaxer-' + ajaxer.id ).find( '.overlay-inner' ).fadeIn( 'fast' );
	$( window ).resize();
	ajaxer.id++;
};

ajaxer.close = function( obj ) {

	var el = $( obj ).parents( '.overlay' );
	el.fadeOut( 'fast', function() {
		$( this ).remove();
	} );
};

var main = {};
main.httpRequest = ajaxer.httpRequest;

$( document ).delegate( 'form.ajaxer', 'submit', function( event ) {
	$( document ).triggerHandler( 'form-submit' );
	event.preventDefault();
	ajaxer.sendForm( this, event );
} );

$( '.ajaxer input' ).live( 'keypress', function( e ) {
	if( e.which == 13 ) {
		$( this ).parents( 'form' ).submit();
	}
} );

$( '.submit' ).live( 'click dblclick', function( e ) {
	$( this ).parents( 'form' ).submit();
	e.preventDefault();
});

ajaxer.watcher = function() {

	var mc = $.cookie( 'query' );
	if( mc ) {
		mc = $.parseJSON( mc );
		ajaxer.query( mc.url );
		$.cookie( 'query', null, { path: "/", domain: config.mainhost ? '.' + config.mainhost : false } );
	}
	mc = $.cookie( 'notify' );
	if( mc ) {
		mc = $.parseJSON( mc );
		if( mc.type == 'error' ) {
			ajaxer.error( mc.lang, mc.message );
		} else {
			ajaxer.notify( mc.lang, mc.message );
		}
		$.cookie( 'notify', null, { path: "/", domain: config.mainhost ? '.' + config.mainhost : false } );
	}
};

$( document ).ready( ajaxer.watcher );
$( document ).bind( 'construct', ajaxer.watcher );